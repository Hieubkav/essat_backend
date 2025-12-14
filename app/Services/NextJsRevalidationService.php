<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NextJsRevalidationService
{
    protected string $revalidationUrl;
    protected string $token;
    protected bool $enabled;

    public function __construct()
    {
        $this->revalidationUrl = config('services.nextjs.revalidation_url', '');
        $this->token = config('services.nextjs.revalidation_token', '');
        $this->enabled = !empty($this->revalidationUrl) && !empty($this->token);
    }

    /**
     * Trigger revalidation cho homepage
     */
    public function revalidateHome(): bool
    {
        return $this->revalidate([
            'type' => 'home',
            'path' => '/',
        ]);
    }

    /**
     * Trigger revalidation cho product pages
     */
    public function revalidateProducts(): bool
    {
        return $this->revalidate([
            'type' => 'product',
            'path' => '/san-pham',
        ]);
    }

    /**
     * Trigger revalidation cho post pages
     */
    public function revalidatePosts(): bool
    {
        return $this->revalidate([
            'type' => 'post',
            'path' => '/bai-viet',
        ]);
    }

    /**
     * Trigger revalidation cho tất cả pages
     */
    public function revalidateAll(): bool
    {
        return $this->revalidate([
            'type' => 'all',
        ]);
    }

    /**
     * Trigger revalidation với custom payload
     */
    public function revalidate(array $payload): bool
    {
        if (!$this->enabled) {
            Log::info('[NextJs Revalidation] Disabled - skipping revalidation', $payload);
            return false;
        }

        try {
            Log::info('[NextJs Revalidation] Sending request', [
                'url' => $this->revalidationUrl,
                'payload' => $payload,
            ]);

            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->revalidationUrl, $payload);

            if ($response->successful()) {
                Log::info('[NextJs Revalidation] Success', [
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::warning('[NextJs Revalidation] Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('[NextJs Revalidation] Exception', [
                'message' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return false;
        }
    }

    /**
     * Check if revalidation is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
