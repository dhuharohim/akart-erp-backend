<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSeriesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'series_number' => $this->series_number,
            'public_id' => $this->public_id,
            'registration_config' => $this->registration_config,
            'checkin_passcode' => $this->checkin_passcode,
            'venue_id' => $this->venue_id,
            'venue' => $this->whenLoaded('venue', fn() => new VenueResource($this->venue)),
            'venues' => $this->whenLoaded('venues', fn() => $this->venues->map(fn($v) => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'address' => $v->address,
                'start_datetime' => $v->pivot->start_datetime,
                'end_datetime' => $v->pivot->end_datetime,
                'amount' => $v->pivot->amount,
                'description' => $v->pivot->description,
                'sort_order' => $v->pivot->sort_order,
            ])),
            'name' => $this->name,
            'status' => $this->status,
            'lifecycle_status' => $this->lifecycleStatus(),
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'budget_amount' => $this->budget_amount,
            'time_schedule' => $this->time_schedule,
            'implementation_instruction' => $this->implementation_instruction,
            'technical_instruction' => $this->technical_instruction,
            'sort_order' => $this->sort_order,
            'require_ticketing' => $this->require_ticketing,
            'vendors_count' => $this->whenCounted('vendors'),
            'staff_count' => $this->whenCounted('staff'),
            'purchase_orders_count' => $this->whenCounted('purchaseOrders'),
            'registrations_count' => $this->whenCounted('registrations'),
            'created_at' => $this->created_at,
        ];
    }

    private function lifecycleStatus(): string
    {
        $status = strtolower((string) $this->status);
        if (in_array($status, ['pra', 'running', 'post', 'completed'], true)) {
            return $status;
        }

        $today = now()->startOfDay();
        $start = $this->start_date ? \Illuminate\Support\Carbon::parse($this->start_date)->startOfDay() : null;
        $end = $this->end_date ? \Illuminate\Support\Carbon::parse($this->end_date)->endOfDay() : null;

        if ($start && $today->lt($start)) {
            return 'pra';
        }
        if ($start && $end && $today->between($start, $end)) {
            return 'running';
        }
        if ($end && $today->gt($end)) {
            return 'post';
        }

        return 'pra';
    }
}
