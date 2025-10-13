<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant(): void
    {
        // Aplica o scope global automaticamente
        static::addGlobalScope(new TenantScope());

        // Ao criar um novo registro, adiciona automaticamente o company_id
        static::creating(function (Model $model) {
            if (!$model->company_id && app()->has('tenant_id')) {
                $model->company_id = app('tenant_id');
            }
        });
    }

    /**
     * Ignora temporariamente o filtro de tenant (para admin)
     */
    public function scopeWithoutTenant($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }

    /**
     * Força o filtro de um tenant específico
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->withoutGlobalScope(TenantScope::class)
                     ->where('company_id', $tenantId);
    }
}

