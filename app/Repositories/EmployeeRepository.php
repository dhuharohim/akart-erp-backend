<?php

namespace App\Repositories;

use App\Models\Employee;

class EmployeeRepository extends BaseRepository
{
    public function __construct(Employee $employee)
    {
        parent::__construct($employee);
    }

    public function paginate(int $perPage = 15)
    {
        return $this->query()
            ->with('team:id,name,team_code')
            ->latest()
            ->paginate($perPage);
    }
}
