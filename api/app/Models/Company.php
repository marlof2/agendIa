<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'person_type',
        'cnpj',
        'cpf',
        'responsible_name',
        'phone_1',
        'has_whatsapp_1',
        'phone_2',
        'has_whatsapp_2',
        'timezone_id',
    ];

    protected $casts = [
        'has_whatsapp_1' => 'boolean',
        'has_whatsapp_2' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the timezone for the company.
     */
    public function timezone(): BelongsTo
    {
        return $this->belongsTo(Timezone::class);
    }

    /**
     * Get the users for the company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('profile_id', 'is_main_company')
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
