<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('events.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $eventId = (int) optional($this->route('event'))->id;

        return [
            'company_id' => ['sometimes', 'nullable', 'integer'],
            'event_number' => ['sometimes', 'string', 'max:100', Rule::unique('events', 'event_number')->ignore($eventId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'type' => ['sometimes', 'nullable', 'string', 'in:corporate_business,social_private,entertainment_arts,sport_wellness,government_formal'],
            'status' => ['sometimes', 'in:pra,running,post,completed'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'timeline' => ['sometimes', 'nullable', 'array'],
            'budget_amount' => ['sometimes', 'numeric', 'min:0'],
            'revenue_amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
