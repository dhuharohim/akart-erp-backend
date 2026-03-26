<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'team_id' => $this->team_id,
            'employee_type' => $this->employee_type,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'salary' => $this->salary,
            'hourly_rate' => $this->hourly_rate,
            'project_rate' => $this->project_rate,
            'team' => $this->team ? [
                'id' => $this->team->id,
                'name' => $this->team->name,
                'team_code' => $this->team->team_code,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
