<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employees) {}

    public function index()
    {
        Gate::authorize('viewAny', Employee::class);

        return EmployeeResource::collection($this->employees->paginate());
    }

    public function store(StoreEmployeeRequest $request)
    {
        $employee = $this->employees->create($request->validated());

        return (new EmployeeResource($employee))->response()->setStatusCode(201);
    }

    public function show(Employee $employee)
    {
        Gate::authorize('view', $employee);

        return new EmployeeResource($employee->load('team'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        Gate::authorize('update', $employee);

        return new EmployeeResource($this->employees->update($employee, $request->validated())->load('team'));
    }

    public function destroy(Employee $employee)
    {
        Gate::authorize('delete', $employee);
        $this->employees->delete($employee);

        return response()->noContent();
    }
}
