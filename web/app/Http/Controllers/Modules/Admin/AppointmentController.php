<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AppointmentController extends Controller
{
    use StoreApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Appointment::with(['customer:id,name,email', 'staff:id,name']);

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('start_at', [$request->start_date, $request->end_date . ' 23:59:59']);
            }

            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('staff_id')) {
                $query->where('staff_id', $request->staff_id);
            }

            $appointments = $query->orderBy('start_at')->get();
            return $this->successResponse($appointments);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'customer_id' => 'nullable|exists:users,id',
                'staff_id' => 'nullable|exists:users,id',
                'start_at' => 'required|date',
                'end_at' => 'nullable|date|after:start_at',
                'type' => 'sometimes|in:meeting,call,visit',
                'location' => 'nullable|string|max:255',
            ]);

            $appointment = Appointment::create($validated);
            return $this->successResponse($appointment->load(['customer', 'staff']), 'Đã tạo lịch hẹn');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'customer_id' => 'nullable|exists:users,id',
                'staff_id' => 'nullable|exists:users,id',
                'start_at' => 'sometimes|date',
                'end_at' => 'nullable|date',
                'type' => 'sometimes|in:meeting,call,visit',
                'status' => 'sometimes|in:scheduled,completed,cancelled',
                'location' => 'nullable|string|max:255',
            ]);

            $appointment->update($validated);
            return $this->successResponse($appointment, 'Đã cập nhật');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            Appointment::findOrFail($id)->delete();
            return $this->successResponse(null, 'Đã xóa lịch hẹn');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
