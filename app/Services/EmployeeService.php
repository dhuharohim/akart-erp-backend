<?php

namespace App\Services;

use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Str;

class EmployeeService
{
    public function __construct(private EmployeeRepository $employees) {}

    public function paginate(int $perPage = 15)
    {
        return $this->employees->paginate($perPage);
    }

    public function create(array $data): Employee
    {
        if (empty($data['employee_code'])) {
            $data['employee_code'] = hash('sha256', Str::uuid()->toString() . microtime(true));
        }
        return $this->employees->create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        return $this->employees->update($employee, $data);
    }

    public function delete(Employee $employee): bool
    {
        return $this->employees->delete($employee);
    }
}
