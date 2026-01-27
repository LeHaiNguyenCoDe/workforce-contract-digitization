<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TaskRepository implements TaskRepositoryInterface
{
    public function getAll(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        $query = Task::with(['assignee:id,name,avatar', 'creator:id,name']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getBoardTasks(): Collection
    {
        // Optimization: Select specific columns and limit scope
        // We get all active tasks, but only recent 'done' tasks to keep the board light
        return Task::select([
                'id', 'title', 'description', 'assignee_id', 'created_by', 
                'status', 'priority', 'due_date', 'created_at', 'updated_at'
            ])
            ->with(['assignee:id,name,avatar', 'creator:id,name'])
            ->where(function ($query) {
                // Get all active tasks
                $query->whereIn('status', ['todo', 'in_progress', 'review'])
                      // OR done tasks from the last 30 days
                      ->orWhere(function ($q) {
                          $q->where('status', 'done')
                            ->where('updated_at', '>=', now()->subDays(30));
                      });
            })
            ->orderBy('priority', 'desc') // High priority first
            ->latest('updated_at')
            ->get();
    }

    public function findById(int $id): ?Task
    {
        return Task::with(['assignee', 'creator'])->find($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task->fresh();
    }

    public function delete(Task $task): bool
    {
        return $task->delete();
    }
}
