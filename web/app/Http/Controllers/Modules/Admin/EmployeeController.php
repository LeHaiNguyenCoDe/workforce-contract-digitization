<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class EmployeeController extends Controller
{
    use StoreApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::with('user:id,name,email,avatar');

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('department')) {
                $query->where('department', $request->department);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $employees = $query->paginate($request->per_page ?? 15);
            return $this->successResponse($employees);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'department' => 'nullable|string|max:100',
                'position' => 'nullable|string|max:100',
                'join_date' => 'nullable|date',
                'base_salary' => 'nullable|numeric|min:0',
                'user_id' => 'nullable|exists:users,id',
            ]);

            // Generate employee code
            $lastEmployee = Employee::orderBy('id', 'desc')->first();
            $sequence = $lastEmployee ? ((int) substr($lastEmployee->employee_code, 3) + 1) : 1;
            $validated['employee_code'] = 'EMP' . str_pad($sequence, 5, '0', STR_PAD_LEFT);

            $employee = Employee::create($validated);
            return $this->successResponse($employee, 'Đã thêm nhân viên');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $employee = Employee::with(['user', 'attendances' => fn($q) => $q->latest()->take(30)])->findOrFail($id);
            return $this->successResponse($employee);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'nullable|email',
                'phone' => 'nullable|string|max:20',
                'department' => 'nullable|string|max:100',
                'position' => 'nullable|string|max:100',
                'join_date' => 'nullable|date',
                'base_salary' => 'nullable|numeric|min:0',
                'status' => 'sometimes|in:active,inactive,on_leave',
            ]);

            $employee->update($validated);
            return $this->successResponse($employee, 'Đã cập nhật');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();
            return $this->successResponse(null, 'Đã xóa nhân viên');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    // Attendance
    public function checkIn(Request $request, int $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
            $today = now()->toDateString();

            $attendance = Attendance::updateOrCreate(
                ['employee_id' => $id, 'date' => $today],
                ['check_in' => now()->format('H:i:s'), 'status' => 'present']
            );

            return $this->successResponse($attendance, 'Check-in thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function checkOut(Request $request, int $id): JsonResponse
    {
        try {
            $today = now()->toDateString();
            $attendance = Attendance::where('employee_id', $id)->where('date', $today)->first();

            if (!$attendance) {
                return $this->errorResponse('Chưa check-in hôm nay', 400);
            }

            $attendance->update(['check_out' => now()->format('H:i:s')]);
            return $this->successResponse($attendance, 'Check-out thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function attendanceReport(Request $request): JsonResponse
    {
        try {
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $report = DB::table('attendances')
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

            return $this->successResponse($report);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
