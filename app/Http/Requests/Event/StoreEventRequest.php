<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('events.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['nullable', 'string', 'in:corporate_business,social_private,entertainment_arts,sport_wellness,government_formal'],
            'venue' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:pra,running,post,completed'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'timeline' => ['nullable', 'array'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'revenue_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
