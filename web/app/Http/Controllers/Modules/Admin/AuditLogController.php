<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Http\Resources\Admin\AuditLogResource;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AuditLogController extends Controller
{
    use StoreApiResponse;

    /**
     * Get all audit logs with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->query('per_page', 20);
            
            $query = AuditLog::with('user:id,name');

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('action')) {
                $query->where('action', $request->action);
            }

            if ($request->has('model_type')) {
                $query->where('model_type', $request->model_type);
            }

            if ($request->has('search')) {
                $search = $request->query('search');
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhere('model_type', 'like', "%{$search}%");
                });
            }

            $logs = $query->latest()->paginate($perPage);

            return $this->paginatedResponse($logs, null, [], AuditLogResource::class);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách nhật ký hệ thống', $e);
        }
    }

    /**
     * Get audit log details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $log = AuditLog::with('user')->findOrFail($id);
            return $this->successResponse(new AuditLogResource($log));
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy thông tin nhật ký', $e);
        }
    }
}
