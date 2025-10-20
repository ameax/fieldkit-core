<?php

declare(strict_types=1);

namespace Ameax\FieldkitCore\Handlers;

use Ameax\FieldkitCore\Contracts\FieldKitMappingHandlerInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailchimpSubscriberHandler implements FieldKitMappingHandlerInterface
{
    /**
     * Can this handler process the mappings?
     */
    public function supports(string $adapter): bool
    {
        return in_array($adapter, ['mailchimp_api', 'mailchimp_subscriber']);
    }

    /**
     * Process the mapped data
     */
    public function handle(Model $model, Collection $mappings, array $formData): void
    {
        $apiKey = config('fieldkit.handlers.mailchimp.api_key');
        $dataCenter = config('fieldkit.handlers.mailchimp.data_center');
        
        if (!$apiKey || !$dataCenter) {
            Log::warning('FieldKit Mailchimp handler: Missing API key or data center configuration');
            return;
        }

        foreach ($mappings as $mapping) {
            $this->processMapping($model, $mapping, $formData, $apiKey, $dataCenter);
        }
    }

    /**
     * Should this handler be executed asynchronously (Queue)?
     */
    public function shouldQueue(): bool
    {
        return true; // API calls should be queued
    }

    /**
     * Process individual mapping
     */
    protected function processMapping(Model $model, array $mapping, array $formData, string $apiKey, string $dataCenter): void
    {
        $config = $mapping['config'] ?? [];
        $listId = $config['list_id'] ?? config('fieldkit.handlers.mailchimp.default_list_id');
        
        if (!$listId) {
            Log::warning('FieldKit Mailchimp handler: No list ID configured for mapping', $mapping);
            return;
        }

        // Extract email from form data or model
        $email = $this->extractEmail($model, $formData, $config);
        if (!$email) {
            Log::warning('FieldKit Mailchimp handler: No email found for subscription');
            return;
        }

        // Prepare subscriber data
        $subscriberData = $this->prepareSubscriberData($email, $formData, $config);
        
        // Make API call
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode('user:' . $apiKey),
                'Content-Type' => 'application/json',
            ])->put(
                "https://{$dataCenter}.api.mailchimp.com/3.0/lists/{$listId}/members/" . md5(strtolower($email)),
                $subscriberData
            );

            if ($response->successful()) {
                Log::info('FieldKit Mailchimp: Successfully subscribed user', [
                    'email' => $email,
                    'list_id' => $listId,
                    'status' => $subscriberData['status']
                ]);
            } else {
                Log::error('FieldKit Mailchimp: Subscription failed', [
                    'email' => $email,
                    'list_id' => $listId,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FieldKit Mailchimp: Exception during subscription', [
                'email' => $email,
                'list_id' => $listId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Extract email from available data
     */
    protected function extractEmail(Model $model, array $formData, array $config): ?string
    {
        // Check form data first
        if (isset($formData['email'])) {
            return $formData['email'];
        }

        // Check configured email field
        if (isset($config['email_field']) && isset($formData[$config['email_field']])) {
            return $formData[$config['email_field']];
        }

        // Check model
        if ($model->hasAttribute('email') && $model->email) {
            return $model->email;
        }

        // Check customer relationship
        if (method_exists($model, 'customer') && $model->customer && $model->customer->email) {
            return $model->customer->email;
        }

        return null;
    }

    /**
     * Prepare subscriber data for Mailchimp API
     */
    protected function prepareSubscriberData(string $email, array $formData, array $config): array
    {
        $data = [
            'email_address' => $email,
            'status' => $config['status'] ?? 'subscribed', // or 'pending' for double opt-in
        ];

        // Add merge fields if configured
        if (isset($config['merge_fields'])) {
            $mergeFields = [];
            foreach ($config['merge_fields'] as $mailchimpField => $formField) {
                if (isset($formData[$formField])) {
                    $mergeFields[$mailchimpField] = $formData[$formField];
                }
            }
            if (!empty($mergeFields)) {
                $data['merge_fields'] = $mergeFields;
            }
        }

        // Add interests/groups if configured
        if (isset($config['interests'])) {
            $interests = [];
            foreach ($config['interests'] as $interestId => $formField) {
                if (isset($formData[$formField])) {
                    $interests[$interestId] = (bool) $formData[$formField];
                }
            }
            if (!empty($interests)) {
                $data['interests'] = $interests;
            }
        }

        // Add tags if configured
        if (isset($config['tags'])) {
            $tags = [];
            foreach ($config['tags'] as $tag) {
                if (is_string($tag)) {
                    $tags[] = ['name' => $tag, 'status' => 'active'];
                } elseif (isset($formData[$tag['field']]) && $formData[$tag['field']]) {
                    $tags[] = ['name' => $tag['name'], 'status' => 'active'];
                }
            }
            if (!empty($tags)) {
                $data['tags'] = $tags;
            }
        }

        return $data;
    }
}