<?php

namespace App\Http\Requests\Journal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('journals.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'journal_number' => ['prohibited'],
            'date' => ['sometimes', 'date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'in:draft,posted,voided'],
            'lines' => ['sometimes', 'array', 'min:1'],
            'lines.*.account_id' => ['required', 'integer', 'exists:chart_of_accounts,id'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
        ];
    }
}
