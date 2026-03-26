<?php

namespace App\Http\Requests\Asset;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assets.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'depreciation_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
