<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function updateOrCreate(array $attributes, array $values = []): Attendance
    {
        return Attendance::updateOrCreate($attributes, $values);
    }

    public function findByEmployeeAndDate(int $employeeId, string $date): ?Attendance
    {
        return Attendance::where('employee_id', $employeeId)->where('date', $date)->first();
    }

    public function getMonthlyReport(int $month, int $year): Collection
    {
        return DB::table('attendances')
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->select(
                'employees.id',
                'employees.name',
                DB::raw('COUNT(*) as total_days'),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late")
            )
            ->groupBy('employees.id', 'employees.name')
            ->get();
    }
}
