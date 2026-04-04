<?php

namespace App\Services;

use App\Jobs\ProcessRegistration;
use App\Models\EventCategory;
use App\Models\EventCategoryPrice;
use App\Models\EventCategoryPrize;
use App\Models\EventRegistration;
use App\Models\EventSeries;
use Illuminate\Support\Facades\Http;

class EventRegistrationService
{
    public function register(EventSeries $series, array $data, string $idempotencyKey): array
    {
        abort_if($series->event->status !== 'pra', 422, 'Registration is not available for this event.');

        // Idempotency check: return existing registration if already processed
        $existing = EventRegistration::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return [
                'registration' => $existing->load(['category', 'priceSeries']),
                'invoice_url' => $existing->xendit_invoice_url,
                'ticket_url' => $this->buildTicketUrl($series, $existing),
            ];
        }

        // Quick quota check before dispatching (non-locking, optimistic)
        $category = EventCategory::where('id', $data['event_category_id'])
            ->where('event_series_id', $series->id)
            ->firstOrFail();

        $activePriceSeries = $series->priceSeries()->currentlyActive()->first()
            ?? $series->priceSeries()->orderByDesc('is_active')->orderByDesc('end_date')->first();

        if ($activePriceSeries) {
            $price = EventCategoryPrice::where('event_price_series_id', $activePriceSeries->id)
                ->where('event_category_id', $category->id)
                ->first();
            $quota = (int) ($price?->quota ?? $category->quota);
            $count = EventRegistration::where('event_category_id', $category->id)
                ->where('event_price_series_id', $activePriceSeries->id)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();

            abort_if(($quota - $count) <= 0, 422, 'This category is sold out for the current pricing period.');
        }

        // Run registration processing synchronously
        ProcessRegistration::dispatchSync($series->id, $data, $idempotencyKey);

        // Fetch the created registration
        $registration = EventRegistration::where('idempotency_key', $idempotencyKey)->first();

        if (! $registration) {
            abort(422, 'Registration failed. Please try again.');
        }

        return [
            'registration' => $registration->load(['category', 'priceSeries']),
            'invoice_url' => $registration->xendit_invoice_url,
            'ticket_url' => $this->buildTicketUrl($series, $registration),
        ];
    }

    public function verifyTurnstile(string $token): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (empty($secret)) {
            return true; // Skip if not configured
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $secret,
            'response' => $token,
        ]);

        return $response->ok() && ($response->json('success') === true);
    }

    public function getPublicSeriesData(EventSeries $series): array
    {
        $activePriceSeries = $series->priceSeries()->currentlyActive()->first();
        $categories = $series->categories()->orderBy('sort_order')->get();

        $prices = [];
        $categoryPrices = collect();
        $categoryPrizes = EventCategoryPrize::query()
            ->where('event_series_id', $series->id)
            ->orderBy('event_category_id')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('event_category_id');
        if ($activePriceSeries) {
            $categoryPrices = EventCategoryPrice::where('event_price_series_id', $activePriceSeries->id)
                ->get()
                ->keyBy('event_category_id');
        }

        foreach ($categories as $cat) {
            $cp = $categoryPrices[$cat->id] ?? null;
            $quota = (int) ($cp?->quota ?? $cat->quota);
            if ($activePriceSeries) {
                $count = EventRegistration::where('event_category_id', $cat->id)
                    ->where('event_price_series_id', $activePriceSeries->id)
                    ->whereIn('payment_status', ['paid', 'pending'])
                    ->count();
            } else {
                $count = EventRegistration::where('event_category_id', $cat->id)
                    ->whereIn('payment_status', ['paid', 'pending'])
                    ->count();
            }
            $remaining = max(0, $quota - $count);

            $prices[] = [
                'category_id' => $cat->id,
                'category_name' => $cat->name,
                'price' => $cp?->price ?? 0,
                'quota' => $quota,
                'remaining_quota' => $remaining,
                'prizes' => ($categoryPrizes[$cat->id] ?? collect())->map(fn ($prize) => [
                    'rank' => $prize->rank,
                    'prize_name' => $prize->prize_name,
                    'prize_value' => $prize->prize_value,
                    'prize_note' => $prize->prize_note,
                ])->values(),
            ];
        }

        $fieldGroups = $series->registrationFieldGroups()
            ->with(['fields' => fn ($q) => $q->where('is_enabled', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($g) => [
                'id' => $g->id,
                'name' => $g->name,
                'fields' => $g->fields->map(fn ($f) => [
                    'id' => $f->id,
                    'field_name' => $f->field_name,
                    'field_label' => $f->field_label,
                    'field_type' => $f->field_type,
                    'is_required' => $f->is_required,
                    'options' => $f->options,
                    'max_length' => $f->max_length,
                ])->values(),
            ])
            ->filter(fn ($g) => $g['fields']->isNotEmpty())
            ->values();

        $config = $series->registration_config ?? [];
        $event = $series->event;

        $series->load('venues.facilities');

        return [
            'event' => [
                'id' => $event->id,
                'event_number' => $event->event_number,
                'name' => $event->name,
                'description' => $event->description,
                'type' => $event->type,
                'status' => $event->status,
            ],
            'series' => [
                'id' => $series->id,
                'series_number' => $series->series_number,
                'name' => $series->name,
                'description' => $series->description,
                'start_date' => $series->start_date,
                'end_date' => $series->end_date,
                'registration_config' => [
                    'telephone_required' => $config['telephone_required'] ?? false,
                    'email_required' => $config['email_required'] ?? false,
                    'age_enabled' => $config['age_enabled'] ?? true,
                    'address_enabled' => $config['address_enabled'] ?? true,
                    'registration_template' => $config['registration_template'] ?? 'clean',
                ],
                'venues' => $series->venues->map(fn ($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'address' => $v->address,
                    'phone' => $v->phone,
                    'max_capacity' => $v->max_capacity,
                    'space_concept' => $v->space_concept,
                    'start_datetime' => $v->pivot->start_datetime,
                    'end_datetime' => $v->pivot->end_datetime,
                    'facilities' => $v->facilities->map(fn ($f) => [
                        'name' => $f->name,
                        'description' => $f->description,
                    ]),
                ]),
            ],
            'active_price_series' => $activePriceSeries ? [
                'id' => $activePriceSeries->id,
                'name' => $activePriceSeries->name,
                'end_date' => $activePriceSeries->end_date,
            ] : null,
            'categories' => $prices,
            'custom_field_groups' => $fieldGroups,
            'contact_persons' => $series->contacts()
                ->with('staff.employee')
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'name' => $c->staff?->employee?->full_name,
                    'role' => $c->label ?? $c->staff?->role_in_event,
                    'phone' => $c->staff?->employee?->phone,
                    'email' => $c->staff?->employee?->email,
                ])->values(),
            'turnstile_site_key' => config('services.turnstile.site_key', ''),
        ];
    }

    public function getPublicTicketData(EventSeries $series, EventRegistration $registration): array
    {
        abort_if($registration->event_series_id !== $series->id, 404, 'Ticket not found.');

        return [
            'event' => [
                'name' => $series->event->name,
                'event_number' => $series->event->event_number,
            ],
            'series' => [
                'name' => $series->name,
                'series_number' => $series->series_number,
                'start_date' => $series->start_date,
                'end_date' => $series->end_date,
            ],
            'registration' => [
                'registration_number' => $registration->registration_number,
                'full_name' => trim($registration->first_name.' '.$registration->last_name),
                'email' => $registration->email,
                'telephone' => $registration->telephone,
                'category' => $registration->category?->name,
                'amount' => $registration->amount,
                'payment_status' => $registration->payment_status,
                'invoice_url' => $registration->xendit_invoice_url,
            ],
            'qr_payload' => $registration->registration_number,
        ];
    }

    private function buildTicketUrl(EventSeries $series, EventRegistration $registration): string
    {
        $baseUrl = rtrim((string) env('PUBLIC_WEB_URL', config('app.url')), '/');

        return "{$baseUrl}/register/{$series->public_id}/ticket/{$registration->registration_number}";
    }
}
