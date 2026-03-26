<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'team_code' => $this->team_code,
            'name' => $this->name,
            'description' => $this->description,
            'lead_employee_id' => $this->lead_employee_id,
            'employees_count' => $this->whenCounted('employees'),
            'lead_employee' => $this->leadEmployee ? [
                'id' => $this->leadEmployee->id,
                'full_name' => $this->leadEmployee->full_name,
            ] : null,
            'employees' => EmployeeResource::collection($this->whenLoaded('employees')),
            'created_at' => $this->created_at,
        ];
    }
}
