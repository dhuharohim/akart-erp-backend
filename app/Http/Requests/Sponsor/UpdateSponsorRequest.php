<?php

namespace App\Http\Requests\Sponsor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSponsorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sponsors.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'nullable', 'integer'],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'website' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
