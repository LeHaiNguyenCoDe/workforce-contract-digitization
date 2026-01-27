<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use Illuminate\Support\Collection;

interface AttendanceRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []): Attendance;
    public function findByEmployeeAndDate(int $employeeId, string $date): ?Attendance;
    public function getMonthlyReport(int $month, int $year): Collection;
}
