<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class TaskController extends Controller
{
    use StoreApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = Task::with(['assignee:id,name,avatar', 'creator:id,name']);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('assignee_id')) {
                $query->where('assignee_id', $request->assignee_id);
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            $tasks = $query->latest()->paginate($request->per_page ?? 50);
            return $this->successResponse($tasks);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function board(): JsonResponse
    {
        try {
            $tasks = Task::with(['assignee:id,name,avatar'])->get();
            $board = [
                'todo' => $tasks->where('status', 'todo')->values(),
                'in_progress' => $tasks->where('status', 'in_progress')->values(),
                'review' => $tasks->where('status', 'review')->values(),
                'done' => $tasks->where('status', 'done')->values(),
            ];
            return $this->successResponse($board);
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
                'assignee_id' => 'nullable|exists:users,id',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'due_date' => 'nullable|date',
                'status' => 'sometimes|in:todo,in_progress,review,done',
            ]);

            $validated['created_by'] = auth()->id();
            $task = Task::create($validated);

            return $this->successResponse($task->load('assignee'), 'Đã tạo task');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'assignee_id' => 'nullable|exists:users,id',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'due_date' => 'nullable|date',
                'status' => 'sometimes|in:todo,in_progress,review,done',
            ]);

            $task->update($validated);
            return $this->successResponse($task->load('assignee'), 'Đã cập nhật');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $task = Task::findOrFail($id);
            $validated = $request->validate([
                'status' => 'required|in:todo,in_progress,review,done',
            ]);

            $task->update($validated);
            return $this->successResponse($task, 'Status updated');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            Task::findOrFail($id)->delete();
            return $this->successResponse(null, 'Đã xóa task');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
