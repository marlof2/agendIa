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
        'has_whatsapp',
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
            'has_whatsapp' => 'boolean',
        ];
    }

    /**
     * Get the profile that owns the user.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
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
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->profile && $this->profile->name === 'admin';
    }

    /**
     * Check if user is secretary.
     */
    public function isSecretary(): bool
    {
        return $this->profile && $this->profile->name === 'secretary';
    }

    /**
     * Check if user is client.
     */
    public function isClient(): bool
    {
        return $this->profile && $this->profile->name === 'client';
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
     * Get the user's abilities grouped by category.
     */
    public function getAbilitiesGrouped(): array
    {
        if (!$this->profile) {
            return [];
        }
        return $this->profile->getAbilitiesGrouped();
    }

    /**
     * Check if user has a specific ability.
     */
    public function hasPermission(string $ability): bool
    {
        if (!$this->profile) {
            return false;
        }
        return $this->profile->abilities->contains('name', $ability);
    }
}
