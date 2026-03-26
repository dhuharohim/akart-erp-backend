<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'nullable', 'integer'],
            'department_id' => ['sometimes', 'nullable', 'integer', 'exists:departments,id'],
            'team_id' => ['sometimes', 'nullable', 'integer', 'exists:teams,id'],
            'employee_type' => ['sometimes', 'in:internal,freelancer'],
            'full_name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'position' => ['sometimes', 'nullable', 'string', 'max:150'],
            'salary' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'hourly_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'project_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }
}
