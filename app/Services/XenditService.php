<?php

namespace App\Services;

use App\Models\EventRegistration;
use Illuminate\Support\Facades\Http;

class XenditService
{
    private string $secretKey;

    private string $baseUrl;

    private string $webhookToken;

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key', 'xnd_development_qj00WUht9xPweu4MLWlP0NknUR9gD2sqvZLZ087NMcN5DTF8WsRXIf8m9uOsV');
        $this->baseUrl = config('services.xendit.base_url', 'https://api.xendit.co');
        $this->webhookToken = config('services.xendit.webhook_token', 'kp0oEpqDYiTvBQaMPuvJTAZnpd83DbeXKMikBjAR4Ph7ovi1');
    }

    public function createInvoice(EventRegistration $registration): array
    {
        $externalId = 'REG-' . $registration->id . '-' . time();

        $successUrl = rtrim((string) env('PUBLIC_WEB_URL', config('app.url')), '/');
        $successUrl .= "/register/{$registration->series->public_id}/ticket/{$registration->registration_number}";


        $customer = [
            'given_names' => substr($registration->first_name, 0, 255),
            'surname' => substr($registration->last_name ?? $registration->first_name, 0, 255),
        ];

        // Only add email if it's actually there
        if (!empty($registration->email)) {
            $customer['email'] = $registration->email;
        }

        // Only add mobile_number if it's actually there AND formatted correctly
        if (!empty($registration->telephone)) {
            $phone = preg_replace('/[^0-9]/', '', $registration->telephone);
            if (str_starts_with($phone, '0')) {
                $phone = '+62' . substr($phone, 1);
            }
            $customer['mobile_number'] = $phone;
        }

        $payload = [
            'external_id' => $externalId,
            'amount' => (int) $registration->amount,
            'description' => "Registration {$registration->registration_number}",
            'currency' => 'IDR',
            'customer' => $customer, // This is now dynamic
            'success_redirect_url' => $successUrl,
            'failure_redirect_url' => $successUrl,
        ];

        $response = Http::withBasicAuth($this->secretKey, '')
            ->post("{$this->baseUrl}/v2/invoices", $payload);

        $response->throw();
        $body = $response->json();

        $registration->update([
            'xendit_external_id' => $externalId,
            'xendit_invoice_id' => $body['id'] ?? null,
            'xendit_invoice_url' => $body['invoice_url'] ?? null,
        ]);

        return $body;
    }

    public function getInvoice(string $invoiceId): ?array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/v2/invoices/{$invoiceId}");

        if (! $response->ok()) {
            return null;
        }

        return $response->json();
    }

    public function listInvoices(array $params = []): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/v2/invoices", $params);

        $response->throw();

        return $response->json();
    }

    public function expireInvoice(string $invoiceId): ?array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post("{$this->baseUrl}/invoices/{$invoiceId}/expire!");

        if (! $response->ok()) {
            return null;
        }

        return $response->json();
    }

    public function verifyWebhookToken(string $token): bool
    {
        return hash_equals($this->webhookToken, $token);
    }

    public function handleCallback(array $payload): void
    {
        $externalId = $payload['external_id'] ?? null;
        if (! $externalId) {
            return;
        }

        $registration = EventRegistration::where('xendit_external_id', $externalId)->first();
        if (! $registration) {
            return;
        }

        $status = $payload['status'] ?? '';

        if ($status === 'PAID' || $status === 'SETTLED') {
            $registration->payment_status = 'paid';
            $registration->paid_at = now();
        } elseif ($status === 'EXPIRED') {
            $registration->payment_status = 'expired';
        } elseif (in_array($status, ['FAILED', 'VOIDED'])) {
            $registration->payment_status = 'failed';
        }

        $registration->save();
    }
}
