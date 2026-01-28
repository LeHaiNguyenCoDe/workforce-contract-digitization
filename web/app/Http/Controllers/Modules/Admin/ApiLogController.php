<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiLog;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ApiLogController extends Controller
{
    use StoreApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = ApiLog::with('user:id,name');

            if ($request->has('method')) {
                $query->where('method', $request->input('method'));
            }

            if ($request->has('status')) {
                if ($request->status === 'error') {
                    $query->where('status_code', '>=', 400);
                } else {
                    $query->where('status_code', '<', 400);
                }
            }

            if ($request->has('endpoint')) {
                $query->where('endpoint', 'like', '%' . $request->endpoint . '%');
            }

            if ($request->has('slow')) {
                $query->where('duration_ms', '>', $request->input('slow', 1000));
            }

            $logs = $query->latest()->paginate($request->per_page ?? 50);
            return $this->paginatedResponse($logs);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function stats(Request $request): JsonResponse
    {
        try {
            $hours = $request->input('hours', 24);
            $since = now()->subHours($hours);

            $stats = [
                'total_requests' => ApiLog::where('created_at', '>=', $since)->count(),
                'error_count' => ApiLog::where('created_at', '>=', $since)->where('status_code', '>=', 400)->count(),
                'avg_duration' => round(ApiLog::where('created_at', '>=', $since)->avg('duration_ms') ?? 0, 2),
                'slow_requests' => ApiLog::where('created_at', '>=', $since)->where('duration_ms', '>', 1000)->count(),
            ];

            // Requests by hour
            $byHour = ApiLog::where('created_at', '>=', $since)
                ->select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour'),
                    DB::raw('COUNT(*) as count'),
                    DB::raw('AVG(duration_ms) as avg_duration')
                )
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();

            // Errors by endpoint
            $errorsByEndpoint = ApiLog::where('created_at', '>=', $since)
                ->where('status_code', '>=', 400)
                ->select('endpoint', DB::raw('COUNT(*) as count'))
                ->groupBy('endpoint')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $stats['requests_by_hour'] = $byHour;
            $stats['errors_by_endpoint'] = $errorsByEndpoint;

            return $this->successResponse($stats);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $log = ApiLog::with('user')->findOrFail($id);
            return $this->successResponse($log);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function cleanup(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $deleted = ApiLog::where('created_at', '<', now()->subDays($days))->delete();
            return $this->successResponse(['deleted' => $deleted], "Đã xóa {$deleted} logs cũ hơn {$days} ngày");
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
