<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'registration_number' => $this->registration_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'telephone' => $this->telephone,
            'age' => $this->age,
            'address' => $this->address,
            'custom_fields' => $this->custom_fields,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'price_series' => $this->whenLoaded('priceSeries', fn() => [
                'id' => $this->priceSeries->id,
                'name' => $this->priceSeries->name,
            ]),
            'amount' => $this->amount,
            'payment_status' => $this->payment_status,
            'paid_at' => $this->paid_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'xendit_invoice_id' => $this->xendit_invoice_id,
            'present_status' => $this->present_status,
            'checked_in_at' => $this->checked_in_at?->toISOString(),
            'scanned_by_type' => $this->scanned_by_type,
            'scanned_by_id' => $this->scanned_by_id,
            'scanned_by_name' => $this->scannedByName(),
            'attendance_dates' => $this->whenLoaded('attendanceRecords', fn () =>
                $this->attendanceRecords->sortBy('date')->pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->values()
            ),
        ];
    }

    private function scannedByName(): ?string
    {
        if (! $this->scanned_by_type || ! $this->scanned_by_id) {
            return null;
        }
        if ($this->scanned_by_type === 'users') {
            $user = \App\Models\User::find($this->scanned_by_id);
            return $user?->name;
        }
        if ($this->scanned_by_type === 'employees') {
            $employee = \App\Models\Employee::find($this->scanned_by_id);
            return $employee?->full_name;
        }
        return null;
    }
}
