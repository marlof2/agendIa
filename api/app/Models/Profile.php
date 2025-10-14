<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
    ];

    /**
     * Get the abilities for the profile.
     */
    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class, 'profile_abilities')
            ->withTimestamps();
    }

    /**
     * Get the users for the profile through company_user pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user', 'profile_id', 'user_id')
            ->withTimestamps();
    }


    /**
     * Get abilities grouped by category.
     */
    public function getAbilitiesGrouped(): array
    {
        $abilities = $this->abilities;
        $grouped = [];

        foreach ($abilities as $ability) {
            $category = $ability->category;

            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            $grouped[$category][] = [
                'id' => $ability->id,
                'name' => $ability->name,
                'action' => $ability->action,
                'display_name' => $ability->display_name,
                'description' => $ability->description
            ];
        }

        return $grouped;
    }
}
