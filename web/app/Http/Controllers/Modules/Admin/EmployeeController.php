<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Http\Resources\Admin\EmployeeResource;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class EmployeeController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private \App\Services\Admin\EmployeeService $employeeService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'department', 'search']);
            $perPage = (int) $request->query('per_page', 50);

            $employees = $this->employeeService->getAll($perPage, $filters);
            return $this->paginatedResponse($employees, null, [], EmployeeResource::class);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách nhân viên', $e);
        }
    }

    public function store(\App\Http\Requests\Modules\Admin\EmployeeRequest $request): JsonResponse
    {
        try {
            $employee = $this->employeeService->create($request->validated());
            return $this->createdResponse(new EmployeeResource($employee), 'Đã thêm nhân viên thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi thêm nhân viên', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $employee = $this->employeeService->getById($id);
            return $this->successResponse(new EmployeeResource($employee));
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy thông tin nhân viên', $e);
        }
    }

    public function update(\App\Http\Requests\Modules\Admin\EmployeeRequest $request, int $id): JsonResponse
    {
        try {
            $employee = $this->employeeService->update($id, $request->validated());
            return $this->updatedResponse(new EmployeeResource($employee), 'Đã cập nhật thông tin nhân viên');
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi cập nhật nhân viên', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->employeeService->delete($id);
            return $this->successResponse(null, 'Đã xóa nhân viên thành công');
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi xóa nhân viên', $e);
        }
    }

    /**
     * Check-in attendance
     */
    public function checkIn(int $id): JsonResponse
    {
        try {
            $attendance = $this->employeeService->checkIn($id);
            return $this->successResponse($attendance, 'Check-in thành công');
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi check-in', $e);
        }
    }

    /**
     * Check-out attendance
     */
    public function checkOut(int $id): JsonResponse
    {
        try {
            $attendance = $this->employeeService->checkOut($id);
            return $this->successResponse($attendance, 'Check-out thành công');
        } catch (\App\Exceptions\BusinessLogicException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi check-out', $e);
        }
    }

    /**
     * Attendance Report
     */
    public function attendanceReport(Request $request): JsonResponse
    {
        try {
            $month = (int) $request->input('month', now()->month);
            $year = (int) $request->input('year', now()->year);

            $report = $this->employeeService->getMonthlyAttendanceReport($month, $year);

            return $this->successResponse($report);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy báo cáo chuyên cần', $e);
        }
    }
}
