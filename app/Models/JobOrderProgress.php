<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderProgress extends Model
{
    protected $table = 'job_order_progress';

    protected $fillable = [
        'job_order_id',
        'user_id',
        'update_type',
        'progress_note',
        'current_location',
        'issues_encountered',
        'estimated_time_remaining',
        'estimated_time_unit',
    ];

    protected $casts = [
        'estimated_time_remaining' => 'integer',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    public function progressUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'accnt_id');
    }

    // Alias for progressUser relationship (for consistency with track view)
    public function updated_by_user(): BelongsTo
    {
        return $this->progressUser();
    }

    public function getEstimatedTimeRemainingFormattedAttribute(): ?string
    {
        if (!$this->estimated_time_remaining)
            return null;
            
        $time = (int) $this->estimated_time_remaining;
        $unit = $this->estimated_time_unit ?? 'days';
        
        return $time . ' ' . $unit;
    }
}
