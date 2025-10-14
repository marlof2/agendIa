<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'phone',
        'cpf',
        'has_whatsapp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'has_whatsapp' => 'boolean',
        ];
    }

    /**
     * Get the profile for a specific company.
     */
    public function getProfileForCompany(int $companyId): ?Profile
    {
        $pivot = $this->companies()
            ->wherePivot('company_id', $companyId)
            ->withPivot('profile_id')
            ->first();

        if (!$pivot || !$pivot->pivot->profile_id) {
            return null;
        }

        return Profile::find($pivot->pivot->profile_id);
    }

    /**
     * Get the companies for the user.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('profile_id', 'is_main_company')
            ->withTimestamps();
    }

    // /**
    //  * Get only the company IDs for the user.
    //  */
    // public function getCompanyIdsAttribute(): array
    // {
    //     return $this->companies()->pluck('companies.id')->toArray();
    // }

    /**
     * Get the appointments where user is the client.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'user_id');
    }

    /**
     * Get the appointments where user is the staff.
     */
    public function staffAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }


    /**
     * Check if user is owner for a specific company.
     */
    public function isOwner(int $companyId): bool
    {
        $profile = $this->getProfileForCompany($companyId);
        return $profile && $profile->name === 'owner';
    }

    /**
     * Check if user is supervisor for a specific company.
     */
    public function isSupervisor(int $companyId): bool
    {
        $profile = $this->getProfileForCompany($companyId);
        return $profile && $profile->name === 'supervisor';
    }

    /**
     * Check if user is professional for a specific company.
     */
    public function isProfessional(int $companyId): bool
    {
        $profile = $this->getProfileForCompany($companyId);
        return $profile && $profile->name === 'professional';
    }

    /**
     * Check if user is client for a specific company.
     */
    public function isClient(int $companyId): bool
    {
        $profile = $this->getProfileForCompany($companyId);
        return $profile && $profile->name === 'client';
    }

    /**
     * Get the user's abilities based on their profile for a specific company.
     */
    public function getAbilities(int $companyId): array
    {
        $profile = $this->getProfileForCompany($companyId);
        if (!$profile) {
            return [];
        }
        return $profile->abilities->pluck('full_name')->toArray();
    }

    /**
     * Get the user's abilities grouped by category for a specific company.
     */
    public function getAbilitiesGrouped(int $companyId): array
    {
        $profile = $this->getProfileForCompany($companyId);
        if (!$profile) {
            return [];
        }
        return $profile->getAbilitiesGrouped();
    }

    /**
     * Check if user has a specific ability for a specific company.
     */
    public function hasPermission(string $ability, int $companyId): bool
    {
        $profile = $this->getProfileForCompany($companyId);
        if (!$profile) {
            return false;
        }
        return $profile->abilities->contains('name', $ability);
    }

    /**
     * Check if user is admin for a specific company.
     */
    public function isAdmin(int $companyId): bool
    {
        $profile = $this->getProfileForCompany($companyId);
        return $profile && $profile->name === 'admin';
    }
}
