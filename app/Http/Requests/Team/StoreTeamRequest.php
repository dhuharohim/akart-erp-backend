<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('teams.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['nullable', 'integer'],
            'team_code' => ['nullable', 'string', 'max:100', 'unique:teams,team_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'lead_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
