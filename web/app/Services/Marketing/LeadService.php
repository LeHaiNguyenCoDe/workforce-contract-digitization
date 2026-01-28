<?php

namespace App\Services\Marketing;

use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class LeadService
{
    /**
     * Get all leads with filters
     */
    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Lead::with(['assignedUser', 'activities' => function ($q) {
            $q->latest()->limit(3);
        }]);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['temperature'])) {
            $query->where('temperature', $filters['temperature']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['min_score'])) {
            $query->where('score', '>=', $filters['min_score']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('score', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create new lead
     */
    public function create(array $data): Lead
    {
        $lead = Lead::create([
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'source' => $data['source'] ?? Lead::SOURCE_WEBSITE,
            'source_detail' => $data['source_detail'] ?? null,
            'score' => $data['score'] ?? 0,
            'status' => Lead::STATUS_NEW,
            'temperature' => Lead::TEMP_COLD,
            'estimated_value' => $data['estimated_value'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        // Create initial activity
        $lead->addActivity(
            LeadActivity::TYPE_NOTE_ADDED,
            'Lead được tạo từ ' . $lead->source,
            null,
            auth()->user()
        );

        // Calculate initial score based on source
        $this->calculateInitialScore($lead);

        return $lead->fresh('assignedUser');
    }

    /**
     * Update lead
     */
    public function update(int $id, array $data): Lead
    {
        $lead = Lead::findOrFail($id);

        $oldStatus = $lead->status;

        $lead->update([
            'full_name' => $data['full_name'] ?? $lead->full_name,
            'email' => $data['email'] ?? $lead->email,
            'phone' => $data['phone'] ?? $lead->phone,
            'company' => $data['company'] ?? $lead->company,
            'status' => $data['status'] ?? $lead->status,
            'estimated_value' => $data['estimated_value'] ?? $lead->estimated_value,
            'expected_close_date' => $data['expected_close_date'] ?? $lead->expected_close_date,
            'notes' => $data['notes'] ?? $lead->notes,
        ]);

        // Log status change
        if (isset($data['status']) && $data['status'] !== $oldStatus) {
            $lead->addActivity(
                LeadActivity::TYPE_STATUS_CHANGED,
                "Trạng thái thay đổi từ {$oldStatus} sang {$lead->status}",
                ['old_status' => $oldStatus, 'new_status' => $lead->status],
                auth()->user()
            );
        }

        return $lead->fresh('assignedUser');
    }

    /**
     * Delete lead
     */
    public function delete(int $id): bool
    {
        $lead = Lead::findOrFail($id);
        return $lead->delete();
    }

    /**
     * Assign lead to user
     */
    public function assignLead(int $leadId, int $userId): Lead
    {
        $lead = Lead::findOrFail($leadId);
        $user = User::findOrFail($userId);

        $lead->assignTo($user);

        $lead->addActivity(
            LeadActivity::TYPE_NOTE_ADDED,
            "Lead được phân công cho {$user->name}",
            ['assigned_to' => $user->id, 'assigned_by' => auth()->id()],
            auth()->user()
        );

        return $lead->fresh('assignedUser');
    }

    /**
     * Convert lead to customer
     */
    public function convertLead(int $leadId, int $userId, ?int $orderId = null): Lead
    {
        $lead = Lead::findOrFail($leadId);
        $user = User::findOrFail($userId);
        $order = $orderId ? Order::find($orderId) : null;

        DB::transaction(function () use ($lead, $user, $order) {
            $lead->convertToCustomer($user, $order);

            $lead->addActivity(
                LeadActivity::TYPE_STATUS_CHANGED,
                "Lead đã chuyển đổi thành khách hàng",
                [
                    'user_id' => $user->id,
                    'order_id' => $order?->id,
                    'converted_by' => auth()->id()
                ],
                auth()->user()
            );

            // Add bonus score for conversion
            $lead->addScore(50, 'Chuyển đổi thành khách hàng');
        });

        return $lead->fresh(['assignedUser', 'convertedUser', 'convertedOrder']);
    }

    /**
     * Add activity to lead
     */
    public function addActivity(int $leadId, string $type, ?string $description = null, ?array $metadata = null): LeadActivity
    {
        $lead = Lead::findOrFail($leadId);

        // Update last contacted time for certain activities
        if (in_array($type, [
            LeadActivity::TYPE_CALL_MADE,
            LeadActivity::TYPE_EMAIL_SENT,
            LeadActivity::TYPE_SMS_SENT,
            LeadActivity::TYPE_MEETING_COMPLETED
        ])) {
            $lead->markAsContacted();
        }

        // Adjust score based on activity
        $this->adjustScoreByActivity($lead, $type);

        return $lead->addActivity($type, $description, $metadata, auth()->user());
    }

    /**
     * Calculate initial score based on source
     */
    protected function calculateInitialScore(Lead $lead): void
    {
        $sourceScores = [
            Lead::SOURCE_WEBSITE => 20,
            Lead::SOURCE_FACEBOOK => 15,
            Lead::SOURCE_GOOGLE => 25,
            Lead::SOURCE_INSTAGRAM => 15,
            Lead::SOURCE_TIKTOK => 10,
            Lead::SOURCE_REFERRAL => 40,
            Lead::SOURCE_EVENT => 30,
            Lead::SOURCE_LANDING_PAGE => 35,
            Lead::SOURCE_COLD_CALL => 5,
            Lead::SOURCE_OTHER => 10,
        ];

        $score = $sourceScores[$lead->source] ?? 10;

        // Bonus for having both email and phone
        if ($lead->email && $lead->phone) {
            $score += 10;
        }

        $lead->updateScore($score, 'Điểm khởi tạo từ nguồn: ' . $lead->source);
    }

    /**
     * Adjust score based on activity
     */
    protected function adjustScoreByActivity(Lead $lead, string $activityType): void
    {
        $scoreChanges = [
            LeadActivity::TYPE_EMAIL_OPENED => 5,
            LeadActivity::TYPE_EMAIL_CLICKED => 10,
            LeadActivity::TYPE_CALL_MADE => 3,
            LeadActivity::TYPE_CALL_RECEIVED => 5,
            LeadActivity::TYPE_MEETING_SCHEDULED => 15,
            LeadActivity::TYPE_MEETING_COMPLETED => 20,
            LeadActivity::TYPE_FORM_FILLED => 25,
            LeadActivity::TYPE_PAGE_VISITED => 2,
        ];

        if (isset($scoreChanges[$activityType])) {
            $lead->addScore($scoreChanges[$activityType], "Hoạt động: {$activityType}");
        }
    }

    /**
     * Get lead statistics
     */
    public function getStats(array $filters = []): array
    {
        $query = Lead::query();

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $new = (clone $query)->where('status', Lead::STATUS_NEW)->count();
        $qualified = (clone $query)->where('status', Lead::STATUS_QUALIFIED)->count();
        $converted = (clone $query)->where('status', Lead::STATUS_CONVERTED)->count();
        $lost = (clone $query)->where('status', Lead::STATUS_LOST)->count();

        $conversionRate = $total > 0 ? round(($converted / $total) * 100, 2) : 0;

        $hot = (clone $query)->where('temperature', Lead::TEMP_HOT)->count();
        $warm = (clone $query)->where('temperature', Lead::TEMP_WARM)->count();
        $cold = (clone $query)->where('temperature', Lead::TEMP_COLD)->count();

        return [
            'total_leads' => $total,
            'by_status' => [
                'new' => $new,
                'qualified' => $qualified,
                'converted' => $converted,
                'lost' => $lost,
            ],
            'by_temperature' => [
                'hot' => $hot,
                'warm' => $warm,
                'cold' => $cold,
            ],
            'conversion_rate' => $conversionRate,
            'total_value' => (clone $query)->sum('estimated_value'),
            'converted_value' => (clone $query)->where('status', Lead::STATUS_CONVERTED)->sum('estimated_value'),
        ];
    }

    /**
     * Get lead pipeline (funnel)
     */
    public function getPipeline(): array
    {
        return [
            'new' => Lead::status(Lead::STATUS_NEW)->count(),
            'contacted' => Lead::status(Lead::STATUS_CONTACTED)->count(),
            'qualified' => Lead::status(Lead::STATUS_QUALIFIED)->count(),
            'proposal' => Lead::status(Lead::STATUS_PROPOSAL)->count(),
            'negotiation' => Lead::status(Lead::STATUS_NEGOTIATION)->count(),
            'converted' => Lead::status(Lead::STATUS_CONVERTED)->count(),
        ];
    }

    /**
     * Bulk import leads from CSV/array
     */
    public function bulkImport(array $leads): array
    {
        $created = 0;
        $failed = 0;
        $errors = [];

        foreach ($leads as $index => $leadData) {
            try {
                $this->create($leadData);
                $created++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'created' => $created,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }
}
