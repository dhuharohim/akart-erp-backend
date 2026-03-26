<?php

namespace App\Http\Requests\ChartOfAccount;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChartOfAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('coa.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:20', 'unique:chart_of_accounts,code,' . $this->route('chart_of_account')?->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:chart_of_accounts,id'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
