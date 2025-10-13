<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleBlock extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'start_datetime',
        'end_datetime',
        'reason',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    /**
     * Get the company that owns the schedule block.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if the block is currently active.
     */
    public function isActive(): bool
    {
        $now = now();
        return $now->between($this->start_datetime, $this->end_datetime);
    }

    /**
     * Check if the block is in the past.
     */
    public function isPast(): bool
    {
        return $this->end_datetime->isPast();
    }

    /**
     * Check if the block is in the future.
     */
    public function isFuture(): bool
    {
        return $this->start_datetime->isFuture();
    }

    /**
     * Get the duration of the block in minutes.
     */
    public function getDurationInMinutes(): int
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Get the duration of the block in hours.
     */
    public function getDurationInHours(): float
    {
        return $this->start_datetime->diffInHours($this->end_datetime);
    }
}
