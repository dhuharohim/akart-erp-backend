<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventSeriesResource;
use App\Models\Event;
use App\Models\EventSeries;
use App\Models\Invoice;
use App\Models\Venue;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class EventSeriesController extends Controller
{
    public function __construct(private DocumentNumberService $documentNumber) {}

    public function index(Event $event)
    {
        Gate::authorize('view', $event);

        $series = $event->series()
            ->with('venues')
            ->withCount(['vendors', 'staff', 'purchaseOrders', 'registrations'])
            ->orderBy('sort_order')
            ->get();

        return EventSeriesResource::collection($series);
    }

    public function store(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:pra,running,post,completed'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'time_schedule' => ['nullable', 'string'],
            'implementation_instruction' => ['nullable', 'string'],
            'technical_instruction' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'require_ticketing' => ['nullable', 'boolean'],
            'venues' => ['nullable', 'array'],
            'venues.*.venue_id' => ['required', 'integer', 'exists:venues,id'],
            'venues.*.start_datetime' => ['nullable', 'date'],
            'venues.*.end_datetime' => ['nullable', 'date'],
            'venues.*.amount' => ['nullable', 'numeric', 'min:0'],
            'venues.*.description' => ['nullable', 'string', 'max:1000'],
            'venues.*.create_invoice' => ['nullable', 'boolean'],
        ]);

        $venuesData = $validated['venues'] ?? [];
        unset($validated['venues']);

        $series = DB::transaction(function () use ($event, $validated, $venuesData) {
            $validated['series_number'] = $this->documentNumber->generate(EventSeries::class);
            $series = $event->series()->create($validated);
            $this->syncVenues($series, $venuesData);

            return $series;
        });

        return (new EventSeriesResource(
            $series->load('venues')->loadCount(['vendors', 'staff', 'purchaseOrders', 'registrations'])
        ))->response()->setStatusCode(201);
    }

    public function show(Event $event, EventSeries $series)
    {
        Gate::authorize('view', $event);

        abort_if($series->event_id !== $event->id, 404);

        return new EventSeriesResource(
            $series->load('venues')->loadCount(['vendors', 'staff', 'purchaseOrders', 'registrations'])
        );
    }

    public function update(Request $request, Event $event, EventSeries $series)
    {
        Gate::authorize('update', $event);

        abort_if($series->event_id !== $event->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:pra,running,post,completed'],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'time_schedule' => ['nullable', 'string'],
            'implementation_instruction' => ['nullable', 'string'],
            'technical_instruction' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'require_ticketing' => ['nullable', 'boolean'],
            'venues' => ['nullable', 'array'],
            'venues.*.venue_id' => ['required', 'integer', 'exists:venues,id'],
            'venues.*.start_datetime' => ['nullable', 'date'],
            'venues.*.end_datetime' => ['nullable', 'date'],
            'venues.*.amount' => ['nullable', 'numeric', 'min:0'],
            'venues.*.description' => ['nullable', 'string', 'max:1000'],
            'venues.*.create_invoice' => ['nullable', 'boolean'],
        ]);

        $venuesData = $validated['venues'] ?? null;
        unset($validated['venues']);

        DB::transaction(function () use ($series, $validated, $venuesData) {
            $series->update($validated);
            if ($venuesData !== null) {
                $this->syncVenues($series, $venuesData);
            }
        });

        return new EventSeriesResource(
            $series->refresh()->load('venues')->loadCount(['vendors', 'staff', 'purchaseOrders', 'registrations'])
        );
    }

    public function updateStatus(Request $request, Event $event, EventSeries $series)
    {
        Gate::authorize('update', $event);
        abort_if($series->event_id !== $event->id, 404);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pra,running,post,completed'],
        ]);

        $series->update(['status' => $validated['status']]);

        return new EventSeriesResource(
            $series->refresh()->load('venues')->loadCount(['vendors', 'staff', 'purchaseOrders', 'registrations'])
        );
    }

    public function regeneratePasscode(Event $event, EventSeries $series)
    {
        Gate::authorize('update', $event);
        abort_if($series->event_id !== $event->id, 404);

        $passcode = $series->regeneratePasscode();

        return response()->json(['data' => ['checkin_passcode' => $passcode]]);
    }

    public function destroy(Event $event, EventSeries $series)
    {
        Gate::authorize('update', $event);

        abort_if($series->event_id !== $event->id, 404);

        $series->delete();

        return response()->noContent();
    }

    private function syncVenues(EventSeries $series, array $venuesData): void
    {
        $syncData = [];
        foreach ($venuesData as $i => $item) {
            $venueId = (int) $item['venue_id'];
            $amount = (float) ($item['amount'] ?? 0);
            $description = !empty($item['description']) ? (string) $item['description'] : null;
            $start = $item['start_datetime'] ?? null;
            $end = $item['end_datetime'] ?? null;
            $createInvoice = (bool) ($item['create_invoice'] ?? false);

            $syncData[$item['venue_id']] = [
                'start_datetime' => $start,
                'end_datetime' => $end,
                'amount' => $amount,
                'description' => $description,
                'sort_order' => $i,
            ];

            if ($createInvoice && $amount > 0) {
                $venue = Venue::query()->find($venueId);
                if ($venue) {
                    $lineDescription = $description ?: "Venue {$venue->name}";
                    $exists = Invoice::query()
                        ->where('related_type', 'event_series')
                        ->where('related_id', $series->id)
                        ->whereHas('details', fn($query) => $query->where('description', $lineDescription))
                        ->exists();
                    if (!$exists) {
                        $invoice = Invoice::query()->create([
                            'company_id' => $series->event->company_id,
                            'related_type' => 'event_series',
                            'related_id' => $series->id,
                            'invoice_number' => $this->documentNumber->generate(Invoice::class),
                            'issue_date' => now()->toDateString(),
                            'due_date' => now()->addDays(14)->toDateString(),
                            'status' => 'issued',
                            'total_amount' => $amount,
                        ]);
                        $invoice->details()->create([
                            'description' => $lineDescription,
                            'unit' => 'service',
                            'quantity' => 1,
                            'unit_price' => $amount,
                            'subtotal' => $amount,
                        ]);
                    }
                }
            }
        }
        $series->venues()->sync($syncData);
    }
}
