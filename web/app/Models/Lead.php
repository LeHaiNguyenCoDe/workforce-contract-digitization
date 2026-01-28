<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    const SOURCE_WEBSITE = 'website';
    const SOURCE_FACEBOOK = 'facebook';
    const SOURCE_GOOGLE = 'google';
    const SOURCE_INSTAGRAM = 'instagram';
    const SOURCE_TIKTOK = 'tiktok';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_EVENT = 'event';
    const SOURCE_COLD_CALL = 'cold_call';
    const SOURCE_LANDING_PAGE = 'landing_page';
    const SOURCE_OTHER = 'other';

    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL = 'proposal';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_CONVERTED = 'converted';
    const STATUS_LOST = 'lost';
    const STATUS_DISQUALIFIED = 'disqualified';

    const TEMP_COLD = 'cold';
    const TEMP_WARM = 'warm';
    const TEMP_HOT = 'hot';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'company',
        'source',
        'source_detail',
        'score',
        'status',
        'temperature',
        'assigned_to',
        'assigned_at',
        'converted_user_id',
        'converted_order_id',
        'converted_at',
        'last_contacted_at',
        'expected_close_date',
        'estimated_value',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'score' => 'integer',
        'assigned_at' => 'datetime',
        'converted_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'expected_close_date' => 'date',
        'estimated_value' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Assigned user
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Converted user
     */
    public function convertedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_user_id');
    }

    /**
     * Converted order
     */
    public function convertedOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    /**
     * Activities
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /**
     * Score histories
     */
    public function scoreHistories(): HasMany
    {
        return $this->hasMany(LeadScoreHistory::class);
    }

    /**
     * Update lead score
     */
    public function updateScore(int $newScore, string $reason): void
    {
        $oldScore = $this->score;
        $this->score = max(0, min(100, $newScore)); // Clamp between 0-100
        $this->save();

        // Log score change
        LeadScoreHistory::create([
            'lead_id' => $this->id,
            'score_before' => $oldScore,
            'score_after' => $this->score,
            'score_change' => $this->score - $oldScore,
            'reason' => $reason,
            'changed_at' => now(),
        ]);

        // Update temperature based on score
        $this->updateTemperature();
    }

    /**
     * Add score points
     */
    public function addScore(int $points, string $reason): void
    {
        $this->updateScore($this->score + $points, $reason);
    }

    /**
     * Subtract score points
     */
    public function subtractScore(int $points, string $reason): void
    {
        $this->updateScore($this->score - $points, $reason);
    }

    /**
     * Update temperature based on score
     */
    protected function updateTemperature(): void
    {
        if ($this->score >= 70) {
            $this->temperature = self::TEMP_HOT;
        } elseif ($this->score >= 40) {
            $this->temperature = self::TEMP_WARM;
        } else {
            $this->temperature = self::TEMP_COLD;
        }
        $this->save();
    }

    /**
     * Mark as contacted
     */
    public function markAsContacted(): void
    {
        $this->last_contacted_at = now();
        if ($this->status === self::STATUS_NEW) {
            $this->status = self::STATUS_CONTACTED;
        }
        $this->save();
    }

    /**
     * Convert to customer
     */
    public function convertToCustomer(User $user, ?Order $order = null): void
    {
        $this->status = self::STATUS_CONVERTED;
        $this->converted_user_id = $user->id;
        $this->converted_order_id = $order?->id;
        $this->converted_at = now();
        $this->save();
    }

    /**
     * Assign to user
     */
    public function assignTo(User $user): void
    {
        $this->assigned_to = $user->id;
        $this->assigned_at = now();
        $this->save();
    }

    /**
     * Add activity
     */
    public function addActivity(string $type, ?string $description = null, ?array $metadata = null, ?User $createdBy = null): LeadActivity
    {
        return $this->activities()->create([
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
            'created_by' => $createdBy?->id,
            'created_at' => now(),
        ]);
    }

    /**
     * Scope: by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: by temperature
     */
    public function scopeTemperature($query, string $temperature)
    {
        return $query->where('temperature', $temperature);
    }

    /**
     * Scope: hot leads
     */
    public function scopeHot($query)
    {
        return $query->where('temperature', self::TEMP_HOT);
    }

    /**
     * Scope: assigned to user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: unassigned
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }
}
