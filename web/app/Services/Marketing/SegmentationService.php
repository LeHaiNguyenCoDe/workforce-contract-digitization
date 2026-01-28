<?php

namespace App\Services\Marketing;

use App\Models\CustomerSegment;
use App\Models\SegmentHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SegmentationService
{
    /**
     * Get all segments with pagination
     */
    public function getAll(int $perPage = 20)
    {
        return CustomerSegment::with('creator')
            ->withCount('customers')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create new segment
     */
    public function create(array $data): CustomerSegment
    {
        $segment = CustomerSegment::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'dynamic',
            'conditions' => $data['conditions'] ?? null,
            'color' => $data['color'] ?? '#3B82F6',
            'icon' => $data['icon'] ?? null,
            'created_by' => auth()->id(),
            'is_active' => $data['is_active'] ?? true,
        ]);

        // If dynamic, calculate immediately
        if ($segment->isDynamic()) {
            $this->calculateDynamicSegment($segment);
        }

        return $segment->fresh(['creator', 'customers']);
    }

    /**
     * Update segment
     */
    public function update(int $id, array $data): CustomerSegment
    {
        $segment = CustomerSegment::findOrFail($id);

        $segment->update([
            'name' => $data['name'] ?? $segment->name,
            'description' => $data['description'] ?? $segment->description,
            'type' => $data['type'] ?? $segment->type,
            'conditions' => $data['conditions'] ?? $segment->conditions,
            'color' => $data['color'] ?? $segment->color,
            'icon' => $data['icon'] ?? $segment->icon,
            'is_active' => $data['is_active'] ?? $segment->is_active,
        ]);

        // Recalculate if dynamic and conditions changed
        if ($segment->isDynamic() && isset($data['conditions'])) {
            $this->calculateDynamicSegment($segment);
        }

        return $segment->fresh(['creator', 'customers']);
    }

    /**
     * Delete segment
     */
    public function delete(int $id): bool
    {
        $segment = CustomerSegment::findOrFail($id);
        return $segment->delete();
    }

    /**
     * Calculate dynamic segment
     */
    public function calculateDynamicSegment(CustomerSegment $segment): void
    {
        if (!$segment->isDynamic()) {
            throw new \Exception('Only dynamic segments can be calculated');
        }

        DB::transaction(function () use ($segment) {
            // Get current customer IDs
            $currentCustomerIds = $segment->customers()->pluck('users.id')->toArray();

            // Get new customer IDs based on conditions
            $newCustomers = $segment->calculateDynamicCustomers();
            $newCustomerIds = $newCustomers->pluck('id')->toArray();

            // Calculate changes
            $customersToAdd = array_diff($newCustomerIds, $currentCustomerIds);
            $customersToRemove = array_diff($currentCustomerIds, $newCustomerIds);

            // Add new customers
            $addData = collect($customersToAdd)->map(function ($userId) {
                return [
                    'user_id' => $userId,
                    'source' => 'auto',
                    'added_at' => now(),
                ];
            });

            if ($addData->isNotEmpty()) {
                $segment->customers()->attach($addData->toArray());
            }

            // Remove old customers
            if (!empty($customersToRemove)) {
                $segment->customers()->detach($customersToRemove);
            }

            // Update segment
            $segment->customer_count = count($newCustomerIds);
            $segment->last_calculated_at = now();
            $segment->save();

            // Create history record
            SegmentHistory::create([
                'segment_id' => $segment->id,
                'customer_count' => $segment->customer_count,
                'customers_added' => count($customersToAdd),
                'customers_removed' => count($customersToRemove),
                'calculated_at' => now(),
            ]);
        });
    }

    /**
     * Add customer to static segment manually
     */
    public function addCustomerToSegment(CustomerSegment $segment, int $userId): void
    {
        if (!$segment->isStatic()) {
            throw new \Exception('Can only manually add customers to static segments');
        }

        $segment->customers()->syncWithoutDetaching([
            $userId => [
                'source' => 'manual',
                'added_at' => now(),
            ]
        ]);

        $segment->increment('customer_count');
    }

    /**
     * Remove customer from static segment
     */
    public function removeCustomerFromSegment(CustomerSegment $segment, int $userId): void
    {
        if (!$segment->isStatic()) {
            throw new \Exception('Can only manually remove customers from static segments');
        }

        $segment->customers()->detach($userId);
        $segment->decrement('customer_count');
    }

    /**
     * Recalculate all active dynamic segments
     */
    public function recalculateAllDynamicSegments(): void
    {
        $segments = CustomerSegment::where('type', 'dynamic')
            ->where('is_active', true)
            ->get();

        foreach ($segments as $segment) {
            $this->calculateDynamicSegment($segment);
        }
    }

    /**
     * Get segment customers with pagination
     */
    public function getSegmentCustomers(int $segmentId, int $perPage = 50)
    {
        $segment = CustomerSegment::findOrFail($segmentId);

        return $segment->customers()
            ->with(['membership.tier', 'points'])
            ->withPivot('source', 'added_at')
            ->paginate($perPage);
    }

    /**
     * Get segments for a customer
     */
    public function getCustomerSegments(int $userId)
    {
        return User::findOrFail($userId)
            ->segments()
            ->where('is_active', true)
            ->get();
    }

    /**
     * Preview segment size before creating
     */
    public function previewSegmentSize(array $conditions): int
    {
        $tempSegment = new CustomerSegment(['conditions' => $conditions, 'type' => 'dynamic']);
        return $tempSegment->calculateDynamicCustomers()->count();
    }
}
