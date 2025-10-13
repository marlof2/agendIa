<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Pega o tenant_id da aplicação
        $tenantId = app('tenant_id', null);

        // Se houver tenant_id definido, aplica o filtro
        if ($tenantId) {
            $builder->where($model->getTable() . '.company_id', $tenantId);
        }
    }
}

