<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teams.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'nullable', 'integer'],
            'team_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('teams', 'team_code')->ignore($this->route('team')?->id),
            ],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'lead_employee_id' => ['sometimes', 'nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
