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
    use HasFactory, Notifiable, HasApiTokens;

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
        'profile_id',
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
        ];
    }

    /**
     * Get the companies for the user.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withTimestamps();
    }

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
     * Get the profile for the user.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Check if user is admin based on profile.
     */
    public function isAdmin(): bool
    {
        return $this->profile && $this->profile->name === 'admin';
    }

    /**
     * Check if user is secretary based on profile.
     */
    public function isSecretary(): bool
    {
        return $this->profile && $this->profile->name === 'secretary';
    }

    /**
     * Check if user is client based on profile.
     */
    public function isClient(): bool
    {
        return $this->profile && $this->profile->name === 'client';
    }

    /**
     * Check if user is owner of a company.
     */
    public function isOwnerOf(Company $company): bool
    {
        return $this->companies()
            ->wherePivot('company_id', $company->id)
            ->exists() && $this->isAdmin();
    }

    /**
     * Check if user is staff of a company.
     */
    public function isStaffOf(Company $company): bool
    {
        return $this->companies()
            ->wherePivot('company_id', $company->id)
            ->exists() && ($this->isAdmin() || $this->isSecretary());
    }

    /**
     * Check if user is client of a company.
     */
    public function isClientOf(Company $company): bool
    {
        return $this->companies()
            ->wherePivot('company_id', $company->id)
            ->exists() && $this->isClient();
    }

    /**
     * Get the user's abilities based on their profile.
     */
    public function getAbilities(): array
    {
        if (!$this->profile) {
            return [];
        }
        return $this->profile->abilities->pluck('full_name')->toArray();
    }

    /**
     * Check if user has a specific ability.
     */
    public function hasPermission(string $ability): bool
    {
        return in_array($ability, $this->getAbilities());
    }

    /**
     * Get user abilities grouped by category.
     */
    public function getAbilitiesGrouped(): array
    {
        if (!$this->profile) {
            return [];
        }

        return $this->profile->getAbilitiesGrouped();
    }
}
