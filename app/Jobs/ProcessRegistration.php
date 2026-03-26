<?php

namespace App\Jobs;

use App\Mail\ETicketMail;
use App\Models\EventCategory;
use App\Models\EventCategoryPrice;
use App\Models\EventRegistration;
use App\Models\EventSeries;
use App\Services\XenditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ProcessRegistration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        private readonly int $seriesId,
        private readonly array $data,
        private readonly string $idempotencyKey,
    ) {}

    public function handle(XenditService $xendit): void
    {
        $series = EventSeries::with('event')->findOrFail($this->seriesId);

        // Idempotency: check if already processed
        $existing = EventRegistration::where('idempotency_key', $this->idempotencyKey)->first();
        if ($existing) {
            return;
        }

        DB::transaction(function () use ($series, $xendit) {
            $category = EventCategory::where('id', $this->data['event_category_id'])
                ->where('event_series_id', $series->id)
                ->lockForUpdate()
                ->firstOrFail();

            $activePriceSeries = $series->priceSeries()->currentlyActive()->first();
            if (! $activePriceSeries) {
                $activePriceSeries = $series->priceSeries()
                    ->orderByDesc('is_active')
                    ->orderByDesc('end_date')
                    ->first();
            }
            if (! $activePriceSeries) {
                $activePriceSeries = $series->priceSeries()->create([
                    'event_id' => $series->event_id,
                    'name' => 'Free Registration',
                    'start_date' => Carbon::now()->startOfDay(),
                    'end_date' => Carbon::now()->addYears(5)->endOfDay(),
                    'is_active' => true,
                    'sort_order' => 999,
                ]);
            }

            $price = EventCategoryPrice::where('event_price_series_id', $activePriceSeries->id)
                ->where('event_category_id', $category->id)
                ->lockForUpdate()
                ->first();

            $amount = (float) ($price?->price ?? 0);
            $quota = (int) ($price?->quota ?? $category->quota);

            $remaining = $quota - EventRegistration::where('event_category_id', $category->id)
                ->where('event_price_series_id', $activePriceSeries->id)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();

            if ($remaining <= 0) {
                // Mark the pending registration as failed
                EventRegistration::where('idempotency_key', $this->idempotencyKey)
                    ->update(['payment_status' => 'failed']);

                return;
            }

            $registration = EventRegistration::create([
                'event_id' => $series->event_id,
                'event_series_id' => $series->id,
                'event_category_id' => $category->id,
                'event_price_series_id' => $activePriceSeries->id,
                'registration_number' => $this->generateTicketNumber(),
                'idempotency_key' => $this->idempotencyKey,
                'first_name' => $this->data['first_name'],
                'last_name' => $this->data['last_name'],
                'email' => $this->data['email'] ?? null,
                'telephone' => $this->data['telephone'] ?? null,
                'age' => $this->data['age'] ?? null,
                'address' => $this->data['address'] ?? null,
                'custom_fields' => $this->data['custom_fields'] ?? null,
                'amount' => $amount,
                'payment_status' => $amount > 0 ? 'pending' : 'paid',
                'paid_at' => $amount > 0 ? null : now(),
            ]);

            // Create Xendit invoice for paid registrations
            if ($amount > 0) {
                $xendit->createInvoice($registration);
            }

            // Send e-ticket email
            $this->sendETicketIfRequired($series, $registration);
        });
    }

    private function generateTicketNumber(): string
    {
        do {
            $code = 'TIX-'.strtoupper(Str::random(6));
        } while (EventRegistration::where('registration_number', $code)->exists());

        return $code;
    }

    private function sendETicketIfRequired(EventSeries $series, EventRegistration $registration): void
    {
        $config = $series->registration_config ?? [];
        $requireEmailTicket = (bool) ($config['email_required'] ?? false);
        if (! $requireEmailTicket || empty($registration->email)) {
            return;
        }

        $baseUrl = rtrim((string) env('PUBLIC_WEB_URL', config('app.url')), '/');
        $ticketUrl = "{$baseUrl}/register/{$series->public_id}/ticket/{$registration->registration_number}";

        try {
            Mail::to($registration->email)->send(new ETicketMail(
                $series->event?->name ?? '',
                $series->name,
                $registration->registration_number,
                $ticketUrl,
            ));
        } catch (\Throwable) {
        }
    }
}
