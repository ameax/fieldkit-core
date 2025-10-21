<?php

declare(strict_types=1);

use Ameax\FieldkitCore\Models\FieldKitDefinition;
use Ameax\FieldkitCore\Models\FieldKitForm;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('evaluates in operator correctly', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'visibility_test',
        'name' => 'Visibility Test',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'dependent_field',
        'type' => 'text',
        'label' => 'Dependent Field',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'email',
                'operator' => 'in',
                'answer_values' => ['rm@ameax.com', 'admin@example.com'],
            ],
        ],
    ]);

    // Should show when email matches one of the values
    expect($field->shouldDisplay(['email' => 'rm@ameax.com']))->toBeTrue();
    expect($field->shouldDisplay(['email' => 'admin@example.com']))->toBeTrue();

    // Should hide when email doesn't match
    expect($field->shouldDisplay(['email' => 'user@test.com']))->toBeFalse();
    expect($field->shouldDisplay([]))->toBeFalse();
});

it('evaluates not_in operator correctly', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'not_in_test',
        'name' => 'Not In Test',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'special_field',
        'type' => 'text',
        'label' => 'Special Field',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'user_type',
                'operator' => 'not_in',
                'answer_values' => ['guest', 'banned'],
            ],
        ],
    ]);

    // Should show when user_type is NOT guest or banned
    expect($field->shouldDisplay(['user_type' => 'admin']))->toBeTrue();
    expect($field->shouldDisplay(['user_type' => 'member']))->toBeTrue();

    // Should hide when user_type is guest or banned
    expect($field->shouldDisplay(['user_type' => 'guest']))->toBeFalse();
    expect($field->shouldDisplay(['user_type' => 'banned']))->toBeFalse();
});

it('evaluates equals operator correctly', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'equals_test',
        'name' => 'Equals Test',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'marketing_consent',
        'type' => 'checkbox',
        'label' => 'Marketing Consent',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'contact_email',
                'operator' => 'equals',
                'answer_values' => ['rm@ameax.com'],
            ],
        ],
    ]);

    // Should show only when email exactly matches
    expect($field->shouldDisplay(['contact_email' => 'rm@ameax.com']))->toBeTrue();

    // Should hide for any other email
    expect($field->shouldDisplay(['contact_email' => 'other@example.com']))->toBeFalse();
    expect($field->shouldDisplay(['contact_email' => 'RM@AMEAX.COM']))->toBeFalse(); // Case sensitive
});

it('evaluates not_equals operator correctly', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'not_equals_test',
        'name' => 'Not Equals Test',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'additional_info',
        'type' => 'textarea',
        'label' => 'Additional Info',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'country',
                'operator' => 'not_equals',
                'answer_values' => ['DE'],
            ],
        ],
    ]);

    // Should show for any country except DE
    expect($field->shouldDisplay(['country' => 'US']))->toBeTrue();
    expect($field->shouldDisplay(['country' => 'FR']))->toBeTrue();
    expect($field->shouldDisplay(['country' => 'UK']))->toBeTrue();

    // Should hide only for DE
    expect($field->shouldDisplay(['country' => 'DE']))->toBeFalse();
});

it('evaluates multiple conditions with AND logic', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'multi_condition',
        'name' => 'Multi Condition Test',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'premium_features',
        'type' => 'checkbox',
        'label' => 'Enable Premium Features',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'user_type',
                'operator' => 'equals',
                'answer_values' => ['premium'],
            ],
            [
                'field_key' => 'country',
                'operator' => 'in',
                'answer_values' => ['US', 'UK', 'CA'],
            ],
        ],
    ]);

    // Should show only when ALL conditions are met
    expect($field->shouldDisplay([
        'user_type' => 'premium',
        'country' => 'US',
    ]))->toBeTrue();

    expect($field->shouldDisplay([
        'user_type' => 'premium',
        'country' => 'UK',
    ]))->toBeTrue();

    // Should hide if ANY condition is not met
    expect($field->shouldDisplay([
        'user_type' => 'basic',
        'country' => 'US',
    ]))->toBeFalse();

    expect($field->shouldDisplay([
        'user_type' => 'premium',
        'country' => 'DE',
    ]))->toBeFalse();

    expect($field->shouldDisplay([
        'user_type' => 'basic',
        'country' => 'DE',
    ]))->toBeFalse();
});

it('handles boolean values correctly', function () {
    $form = FieldKitForm::create([
        'purpose_token' => 'boolean_test',
        'name' => 'Boolean Test',
        'is_active' => true,
    ]);

    $field = FieldKitDefinition::create([
        'fieldkit_form_id' => $form->id,
        'key' => 'newsletter_frequency',
        'type' => 'select',
        'label' => 'Newsletter Frequency',
        'is_active' => true,
        'conditions' => [
            [
                'field_key' => 'subscribe_newsletter',
                'operator' => 'equals',
                'answer_values' => ['true'],
            ],
        ],
    ]);

    // Boolean true should be converted to 'true' string
    expect($field->shouldDisplay(['subscribe_newsletter' => true]))->toBeTrue();

    // Boolean false should be converted to 'false' string
    expect($field->shouldDisplay(['subscribe_newsletter' => false]))->toBeFalse();

    // String 'true' should work directly
    expect($field->shouldDisplay(['subscribe_newsletter' => 'true']))->toBeTrue();
});
