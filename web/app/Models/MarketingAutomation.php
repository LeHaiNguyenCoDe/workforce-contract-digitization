<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAutomation extends Model
{
    use HasFactory;

    protected $table = 'marketing_automations';

    protected $fillable = [
        'name',
        'trigger_type',
        'delay_days',
        'delay_hours',
        'action_type',
        'email_template_id',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'delay_days' => 'integer',
        'delay_hours' => 'integer',
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Append virtual attributes for frontend compatibility
     */
    protected $appends = ['trigger', 'action'];

    /**
     * Get trigger (alias for trigger_type)
     */
    public function getTriggerAttribute(): string
    {
        return $this->trigger_type;
    }

    /**
     * Get action (alias for action_type)
     */
    public function getActionAttribute(): string
    {
        return $this->action_type;
    }

    /**
     * Get email template
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }
}
