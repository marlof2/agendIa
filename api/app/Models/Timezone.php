<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timezone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'region',
        'offset',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the companies for the timezone.
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
