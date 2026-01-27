<?php

namespace App\Services\Admin;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Exceptions\NotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository
    ) {
    }

    public function getAll(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        return $this->taskRepository->getAll($perPage, $filters);
    }

    public function getBoardData(): array
    {
        // Cache board data for 5 minutes, as this is a heavy dashboard query
        return Cache::remember('board_tasks', 300, function () {
            $tasks = $this->taskRepository->getBoardTasks();
            
            return [
                'todo' => $tasks->where('status', 'todo')->values(),
                'in_progress' => $tasks->where('status', 'in_progress')->values(),
                'review' => $tasks->where('status', 'review')->values(),
                'done' => $tasks->where('status', 'done')->values(),
            ];
        });
    }

    public function getById(int $id): Task
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new NotFoundException("Task not found");
        }
        return $task;
    }

    public function create(array $data): Task
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        
        $task = $this->taskRepository->create($data);
        $this->clearBoardCache();
        return $task;
    }

    public function update(int $id, array $data): Task
    {
        $task = $this->getById($id);
        $task = $this->taskRepository->update($task, $data);
        $this->clearBoardCache();
        return $task;
    }

    public function updateStatus(int $id, string $status): Task
    {
        $task = $this->getById($id);
        $task = $this->taskRepository->update($task, ['status' => $status]);
        $this->clearBoardCache();
        return $task;
    }

    public function delete(int $id): bool
    {
        $task = $this->getById($id);
        $result = $this->taskRepository->delete($task);
        if ($result) {
            $this->clearBoardCache();
        }
        return $result;
    }

    private function clearBoardCache(): void
    {
        Cache::forget('board_tasks');
    }
}
