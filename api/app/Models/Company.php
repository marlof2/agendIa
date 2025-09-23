<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cnpj',
        'phone',
        'whatsapp_number',
        'timezone',
    ];

    protected $casts = [
        'timezone' => 'string',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the users for the company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
                    ->withTimestamps();
    }

    /**
     * Get the services for the company.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the schedules for the company.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the schedule blocks for the company.
     */
    public function scheduleBlocks(): HasMany
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    /**
     * Get the appointments for the company.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the owner of the company (admin users).
     */
    public function owner()
    {
        return $this->users()->whereHas('profile', function ($query) {
            $query->where('name', 'admin');
        })->first();
    }

    /**
     * Get the staff members of the company (admin and secretary users).
     */
    public function staff()
    {
        return $this->users()->whereHas('profile', function ($query) {
            $query->whereIn('name', ['admin', 'secretary']);
        });
    }

    /**
     * Get the clients of the company.
     */
    public function clients()
    {
        return $this->users()->whereHas('profile', function ($query) {
            $query->where('name', 'client');
        });
    }

    /**
     * Check if company is active (not soft deleted).
     */
    public function isActive(): bool
    {
        return !$this->trashed();
    }

    /**
     * Soft delete the company (mark as inactive).
     */
    public function deactivate(): bool
    {
        return $this->delete();
    }

    /**
     * Restore the company (mark as active).
     */
    public function activate(): bool
    {
        return $this->restore();
    }
}
