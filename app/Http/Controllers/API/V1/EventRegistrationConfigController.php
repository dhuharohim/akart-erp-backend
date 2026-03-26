<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Models\EventCategoryPrice;
use App\Models\EventCategoryPrize;
use App\Models\EventPriceSeries;
use App\Models\EventRegistrationField;
use App\Models\EventSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventRegistrationConfigController extends Controller
{
    private function authorizeView(EventSeries $series): void
    {
        Gate::authorize('view', $series->event);
    }

    private function authorizeUpdate(EventSeries $series): void
    {
        Gate::authorize('update', $series->event);
    }

    // --- Price Series ---

    public function indexPriceSeries(EventSeries $series)
    {
        $this->authorizeView($series);

        return response()->json([
            'data' => $series->priceSeries()->orderBy('sort_order')->get(),
        ]);
    }

    public function storePriceSeries(Request $request, EventSeries $series)
    {
        $this->authorizeUpdate($series);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $priceSeries = $series->priceSeries()->create(array_merge($data, ['event_id' => $series->event_id]));

        return response()->json(['data' => $priceSeries], 201);
    }

    public function updatePriceSeries(Request $request, EventSeries $series, EventPriceSeries $priceSeries)
    {
        $this->authorizeUpdate($series);
        abort_if($priceSeries->event_series_id !== $series->id, 404);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $priceSeries->update($data);

        return response()->json(['data' => $priceSeries]);
    }

    public function destroyPriceSeries(EventSeries $series, EventPriceSeries $priceSeries)
    {
        $this->authorizeUpdate($series);
        abort_if($priceSeries->event_series_id !== $series->id, 404);
        $priceSeries->delete();

        return response()->noContent();
    }

    // --- Categories ---

    public function indexCategories(EventSeries $series)
    {
        $this->authorizeView($series);

        return response()->json([
            'data' => $series->categories()->orderBy('sort_order')->get(),
        ]);
    }

    public function storeCategory(Request $request, EventSeries $series)
    {
        $this->authorizeUpdate($series);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'quota' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $category = $series->categories()->create(array_merge($data, ['event_id' => $series->event_id]));

        return response()->json(['data' => $category], 201);
    }

    public function updateCategory(Request $request, EventSeries $series, EventCategory $category)
    {
        $this->authorizeUpdate($series);
        abort_if($category->event_series_id !== $series->id, 404);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'quota' => 'sometimes|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update($data);

        return response()->json(['data' => $category]);
    }

    public function destroyCategory(EventSeries $series, EventCategory $category)
    {
        $this->authorizeUpdate($series);
        abort_if($category->event_series_id !== $series->id, 404);
        $category->delete();

        return response()->noContent();
    }

    // --- Price Matrix ---

    public function getPriceMatrix(EventSeries $series)
    {
        $this->authorizeView($series);

        $prices = EventCategoryPrice::whereHas('series', fn ($q) => $q->where('event_series_id', $series->id))
            ->with(['series:id,name', 'category:id,name'])
            ->get();

        return response()->json(['data' => $prices]);
    }

    public function savePriceMatrix(Request $request, EventSeries $series)
    {
        $this->authorizeUpdate($series);
        $data = $request->validate([
            'prices' => 'required|array|min:1',
            'prices.*.event_price_series_id' => 'required|integer|exists:event_price_series,id',
            'prices.*.event_category_id' => 'required|integer|exists:event_categories,id',
            'prices.*.price' => 'required|numeric|min:0',
            'prices.*.quota' => 'required|integer|min:0',
        ]);

        foreach ($data['prices'] as $item) {
            EventCategoryPrice::updateOrCreate(
                [
                    'event_price_series_id' => $item['event_price_series_id'],
                    'event_category_id' => $item['event_category_id'],
                ],
                [
                    'price' => $item['price'],
                    'quota' => $item['quota'],
                ],
            );
        }

        return response()->json(['message' => 'Price matrix saved.']);
    }

    // --- Category Prizes ---

    public function indexCategoryPrizes(EventSeries $series)
    {
        $this->authorizeView($series);

        return response()->json([
            'data' => EventCategoryPrize::query()
                ->where('event_series_id', $series->id)
                ->with('category:id,name')
                ->orderBy('event_category_id')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function saveCategoryPrizes(Request $request, EventSeries $series)
    {
        $this->authorizeUpdate($series);
        $data = $request->validate([
            'prizes' => 'required|array',
            'prizes.*.event_category_id' => 'required|integer|exists:event_categories,id',
            'prizes.*.rank' => 'required|integer|min:1',
            'prizes.*.prize_name' => 'required|string|max:255',
            'prizes.*.prize_value' => 'nullable|numeric|min:0',
            'prizes.*.prize_note' => 'nullable|string|max:500',
            'prizes.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $categoryIds = $series->categories()->pluck('id')->all();

        EventCategoryPrize::query()
            ->where('event_series_id', $series->id)
            ->delete();

        foreach ($data['prizes'] as $item) {
            if (! in_array((int) $item['event_category_id'], $categoryIds, true)) {
                continue;
            }
            EventCategoryPrize::query()->create([
                'event_id' => $series->event_id,
                'event_series_id' => $series->id,
                'event_category_id' => $item['event_category_id'],
                'rank' => $item['rank'],
                'prize_name' => $item['prize_name'],
                'prize_value' => $item['prize_value'] ?? null,
                'prize_note' => $item['prize_note'] ?? null,
                'sort_order' => $item['sort_order'] ?? $item['rank'],
            ]);
        }

        return response()->json([
            'data' => EventCategoryPrize::query()
                ->where('event_series_id', $series->id)
                ->with('category:id,name')
                ->orderBy('event_category_id')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    // --- Registration Fields ---

    public function indexRegistrationFields(EventSeries $series)
    {
        $this->authorizeView($series);

        return response()->json([
            'data' => $series->registrationFields()->orderBy('sort_order')->get(),
        ]);
    }

    public function syncRegistrationFields(Request $request, EventSeries $series)
    {
        $this->authorizeUpdate($series);
        $data = $request->validate([
            'fields' => 'required|array',
            'fields.*.field_name' => 'required|string|max:100',
            'fields.*.field_label' => 'required|string|max:255',
            'fields.*.field_type' => 'required|in:text,textarea,number,select,date,email,tel',
            'fields.*.is_required' => 'nullable|boolean',
            'fields.*.is_enabled' => 'nullable|boolean',
            'fields.*.options' => 'nullable|array',
            'fields.*.sort_order' => 'nullable|integer',
        ]);

        $series->registrationFields()->delete();

        foreach ($data['fields'] as $field) {
            $series->registrationFields()->create(array_merge($field, ['event_id' => $series->event_id]));
        }

        return response()->json([
            'data' => $series->registrationFields()->orderBy('sort_order')->get(),
        ]);
    }

    // --- Registration Config ---

    public function getRegistrationConfig(EventSeries $series)
    {
        $this->authorizeView($series);

        $config = $series->registration_config ?? [];

        return response()->json([
            'data' => [
                'telephone_required' => $config['telephone_required'] ?? false,
                'email_required' => $config['email_required'] ?? false,
                'age_enabled' => $config['age_enabled'] ?? true,
                'address_enabled' => $config['address_enabled'] ?? true,
                'registration_template' => $config['registration_template'] ?? 'clean',
            ],
        ]);
    }

    public function updateRegistrationConfig(Request $request, EventSeries $series)
    {
        $this->authorizeUpdate($series);
        $data = $request->validate([
            'telephone_required' => 'nullable|boolean',
            'email_required' => 'nullable|boolean',
            'age_enabled' => 'nullable|boolean',
            'address_enabled' => 'nullable|boolean',
            'registration_template' => 'nullable|string|in:clean,split',
        ]);

        $existing = $series->registration_config ?? [];
        $series->update(['registration_config' => array_merge($existing, $data)]);

        return response()->json(['data' => $series->registration_config]);
    }
}
