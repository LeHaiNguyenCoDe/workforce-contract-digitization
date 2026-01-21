<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Http\Resources\Admin\TaskResource;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class TaskController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private \App\Services\Admin\TaskService $taskService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'assignee_id', 'priority']);
            $perPage = (int) $request->query('per_page', 50);

            $tasks = $this->taskService->getAll($perPage, $filters);
            return $this->paginatedResponse(TaskResource::collection($tasks));
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy danh sách task', $e);
        }
    }

    public function board(): JsonResponse
    {
        try {
            $board = $this->taskService->getBoardData();
            
            $formattedBoard = [
                'todo' => TaskResource::collection($board['todo']),
                'in_progress' => TaskResource::collection($board['in_progress']),
                'review' => TaskResource::collection($board['review']),
                'done' => TaskResource::collection($board['done']),
            ];

            return $this->successResponse($formattedBoard);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy dữ liệu bảng task', $e);
        }
    }

    public function store(\App\Http\Requests\Modules\Admin\TaskRequest $request): JsonResponse
    {
        try {
            $task = $this->taskService->create($request->validated());
            return $this->createdResponse(new TaskResource($task->load('assignee')), 'Đã tạo task thành công');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi tạo task', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getById($id);
            return $this->successResponse(new TaskResource($task));
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi lấy thông tin task', $e);
        }
    }

    public function update(\App\Http\Requests\Modules\Admin\TaskRequest $request, int $id): JsonResponse
    {
        try {
            $task = $this->taskService->update($id, $request->validated());
            return $this->updatedResponse(new TaskResource($task->load('assignee')), 'Đã cập nhật task');
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi cập nhật task', $e);
        }
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:todo,in_progress,review,done',
            ]);

            $task = $this->taskService->updateStatus($id, $validated['status']);
            return $this->updatedResponse(new TaskResource($task), 'Trạng thái task đã được cập nhật');
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi cập nhật trạng thái task', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->delete($id);
            return $this->successResponse(null, 'Đã xóa task thành công');
        } catch (\App\Exceptions\NotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->serverErrorResponse('Lỗi khi xóa task', $e);
        }
    }
}
