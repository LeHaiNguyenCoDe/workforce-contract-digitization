<?php

namespace App\Services\Admin;

use App\Models\Employee;
use App\Models\Attendance;
use App\Repositories\Contracts\EmployeeRepositoryInterface;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessLogicException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository,
        private AttendanceRepositoryInterface $attendanceRepository
    ) {
    }

    public function getAll(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->employeeRepository->getAll($perPage, $filters);
    }

    public function getById(int $id): Employee
    {
        $employee = $this->employeeRepository->findById($id);
        if (!$employee) {
            throw new NotFoundException("Employee not found");
        }
        return $employee;
    }

    public function create(array $data): Employee
    {
        // Generate employee code
        $lastEmployee = $this->employeeRepository->findLast();
        $sequence = $lastEmployee ? ((int) substr($lastEmployee->employee_code, 3) + 1) : 1;
        $data['employee_code'] = 'EMP' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        return $this->employeeRepository->create($data);
    }

    public function update(int $id, array $data): Employee
    {
        $employee = $this->getById($id);
        return $this->employeeRepository->update($employee, $data);
    }

    public function delete(int $id): bool
    {
        $employee = $this->getById($id);
        return $this->employeeRepository->delete($employee);
    }

    public function checkIn(int $employeeId): Attendance
    {
        $this->getById($employeeId);
        $today = now()->toDateString();

        return $this->attendanceRepository->updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $today],
            ['check_in' => now()->format('H:i:s'), 'status' => 'present']
        );
    }

    public function checkOut(int $employeeId): Attendance
    {
        $today = now()->toDateString();
        $attendance = $this->attendanceRepository->findByEmployeeAndDate($employeeId, $today);

        if (!$attendance) {
            throw new BusinessLogicException('Chưa check-in hôm nay');
        }

        return $this->attendanceRepository->updateOrCreate(
            ['id' => $attendance->id],
            ['check_out' => now()->format('H:i:s')]
        );
    }

    public function getMonthlyAttendanceReport(int $month, int $year): Collection
    {
        return $this->attendanceRepository->getMonthlyReport($month, $year);
    }
}
