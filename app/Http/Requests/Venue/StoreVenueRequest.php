<?php

namespace App\Http\Requests\Venue;

use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('venues.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'space_concept' => ['nullable', 'string', 'in:indoor,outdoor,semi-outdoor'],
            'type' => ['nullable', 'string', 'in:mice_corporate,wedding,concert,art,social,sports'],
            'facilities' => ['nullable', 'array'],
            'facilities.*.name' => ['required', 'string', 'max:255'],
            'facilities.*.description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
