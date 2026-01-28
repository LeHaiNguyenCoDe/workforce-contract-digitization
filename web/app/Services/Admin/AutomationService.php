<?php

namespace App\Services\Admin;

use App\Models\MarketingAutomation;
use Illuminate\Pagination\LengthAwarePaginator;

class AutomationService
{
    /**
     * Get all automations
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return MarketingAutomation::orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get by ID
     */
    public function getById(int $id): MarketingAutomation
    {
        return MarketingAutomation::findOrFail($id);
    }

    /**
     * Create automation
     */
    public function create(array $data): MarketingAutomation
    {
        // Map FE fields if necessary
        $mapped = $this->mapData($data);
        return MarketingAutomation::create($mapped);
    }

    /**
     * Update automation
     */
    public function update(int $id, array $data): MarketingAutomation
    {
        $automation = $this->getById($id);
        $mapped = $this->mapData($data);
        $automation->update($mapped);
        return $automation;
    }

    /**
     * Delete automation
     */
    public function delete(int $id): bool
    {
        $automation = $this->getById($id);
        return $automation->delete();
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): MarketingAutomation
    {
        $automation = $this->getById($id);
        $automation->is_active = !$automation->is_active;
        $automation->save();
        return $automation;
    }

    /**
     * Map frontend fields to database fields
     */
    protected function mapData(array $data): array
    {
        return [
            'name' => $data['name'] ?? '',
            'trigger_type' => $data['trigger'] ?? $data['trigger_type'] ?? 'order_placed',
            'action_type' => $data['action'] ?? $data['action_type'] ?? 'email',
            'delay_days' => $data['delay_days'] ?? 0,
            'delay_hours' => $data['delay_hours'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'conditions' => $data['conditions'] ?? null,
            'email_template_id' => $data['email_template_id'] ?? null,
        ];
    }
}
