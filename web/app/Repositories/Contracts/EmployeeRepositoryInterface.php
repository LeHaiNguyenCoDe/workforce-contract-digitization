<?php

namespace App\Repositories\Contracts;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?Employee;
    public function create(array $data): Employee;
    public function update(Employee $employee, array $data): Employee;
    public function delete(Employee $employee): bool;
    public function findLast(): ?Employee;
}
