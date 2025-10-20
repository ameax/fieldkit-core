<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Handlers\AmeaxCustomerHandler;
use Ameax\FieldkitCore\Handlers\MailchimpSubscriberHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->customer = \App\Models\Customer::factory()->create([
        'email' => 'test@example.com',
    ]);
});

it('ameax customer handler supports ameax_column adapter', function () {
    $handler = new AmeaxCustomerHandler();
    
    expect($handler->supports('ameax_column'))->toBeTrue();
    expect($handler->supports('mailchimp_api'))->toBeFalse();
    expect($handler->shouldQueue())->toBeFalse();
});

it('ameax customer handler processes simple column mapping', function () {
    $handler = new AmeaxCustomerHandler();
    
    $mappings = collect([
        [
            'adapter' => 'ameax_column',
            'target' => 'xcu_newsletter',
            'field_key' => 'newsletter',
            'config' => ['type' => 'boolean'],
        ]
    ]);
    
    $formData = [
        'newsletter' => true,
    ];
    
    $handler->handle($this->customer, $mappings, $formData);
    
    $this->customer->refresh();
    expect($this->customer->xcu_newsletter)->toBe(1);
});

it('ameax customer handler processes nested attribute mapping', function () {
    $handler = new AmeaxCustomerHandler();
    
    $mappings = collect([
        [
            'adapter' => 'ameax_column',
            'target' => 'phone',
            'field_key' => 'phone',
        ]
    ]);
    
    $formData = [
        'phone' => '+49 123 456789',
    ];
    
    $handler->handle($this->customer, $mappings, $formData);
    
    $this->customer->refresh();
    expect($this->customer->phone)->toBe('+49 123 456789');
});

it('mailchimp handler supports mailchimp adapters', function () {
    $handler = new MailchimpSubscriberHandler();
    
    expect($handler->supports('mailchimp_api'))->toBeTrue();
    expect($handler->supports('mailchimp_subscriber'))->toBeTrue();
    expect($handler->supports('ameax_column'))->toBeFalse();
    expect($handler->shouldQueue())->toBeTrue();
});

it('mailchimp handler processes subscription with mocked API', function () {
    Http::fake([
        'https://us1.api.mailchimp.com/*' => Http::response([
            'email_address' => 'test@example.com',
            'status' => 'subscribed',
        ], 200)
    ]);
    
    config([
        'fieldkit.handlers.mailchimp.api_key' => 'test-api-key',
        'fieldkit.handlers.mailchimp.data_center' => 'us1',
        'fieldkit.handlers.mailchimp.default_list_id' => 'list123',
    ]);
    
    $handler = new MailchimpSubscriberHandler();
    
    $mappings = collect([
        [
            'adapter' => 'mailchimp_subscriber',
            'target' => 'subscription',
            'field_key' => 'newsletter',
            'config' => [
                'list_id' => 'list123',
                'status' => 'subscribed',
            ],
        ]
    ]);
    
    $formData = [
        'newsletter' => true,
        'email' => 'test@example.com',
    ];
    
    $handler->handle($this->customer, $mappings, $formData);
    
    Http::assertSent(function ($request) {
        return $request->url() === 'https://us1.api.mailchimp.com/3.0/lists/list123/members/' . md5('test@example.com') &&
               $request['email_address'] === 'test@example.com' &&
               $request['status'] === 'subscribed';
    });
});

it('mailchimp handler handles missing configuration gracefully', function () {
    config([
        'fieldkit.handlers.mailchimp.api_key' => null,
        'fieldkit.handlers.mailchimp.data_center' => null,
    ]);
    
    $handler = new MailchimpSubscriberHandler();
    
    $mappings = collect([
        [
            'adapter' => 'mailchimp_subscriber',
            'target' => 'subscription',
            'field_key' => 'newsletter',
        ]
    ]);
    
    $formData = [
        'newsletter' => true,
        'email' => 'test@example.com',
    ];
    
    // Should not throw exception, just log warning
    $handler->handle($this->customer, $mappings, $formData);
    
    // No HTTP requests should be made
    Http::assertNothingSent();
});

it('mailchimp handler extracts email from various sources', function () {
    $handler = new MailchimpSubscriberHandler();
    
    // Test reflection to access protected method
    $reflection = new ReflectionClass($handler);
    $method = $reflection->getMethod('extractEmail');
    $method->setAccessible(true);
    
    // Email from form data
    $email = $method->invoke($handler, $this->customer, ['email' => 'form@example.com'], []);
    expect($email)->toBe('form@example.com');
    
    // Email from model when not in form
    $email = $method->invoke($handler, $this->customer, [], []);
    expect($email)->toBe('test@example.com');
    
    // Email from configured field
    $email = $method->invoke($handler, $this->customer, ['user_email' => 'config@example.com'], ['email_field' => 'user_email']);
    expect($email)->toBe('config@example.com');
});

it('processes full integration with config-based form', function () {
    config([
        'fieldkit.forms.customer_registration' => [
            'name' => 'Customer Registration',
            'handlers' => [
                AmeaxCustomerHandler::class,
            ],
            'fields' => [
                [
                    'key' => 'newsletter',
                    'type' => 'checkbox',
                    'label' => 'Subscribe to newsletter',
                    'mappings' => [
                        [
                            'adapter' => 'ameax_column',
                            'target' => 'xcu_newsletter',
                            'field_key' => 'newsletter',
                            'config' => ['type' => 'boolean'],
                        ],
                    ],
                ],
            ],
        ],
    ]);
    
    $service = app(\Ameax\FieldkitCore\Services\FieldKitService::class);
    
    $formData = [
        'newsletter' => true,
    ];
    
    $service->storeData('customer_registration', $this->customer, $formData);
    
    $this->customer->refresh();
    
    // Check JSON storage
    expect($this->customer->fieldkit_data)->toBe(['newsletter' => true]);
    
    // Check Ameax column mapping
    expect($this->customer->xcu_newsletter)->toBe(1);
});