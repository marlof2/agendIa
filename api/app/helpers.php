<?php

if (!function_exists('tenant_id')) {
    /**
     * Get the current tenant ID
     */
    function tenant_id(): ?int
    {
        return app('tenant_id', null);
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if the authenticated user is admin
     */
    function is_admin(): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }
}

if (!function_exists('bypass_tenant_scope')) {
    /**
     * Execute a callback bypassing tenant scope (for admin operations)
     */
    function bypass_tenant_scope(callable $callback)
    {
        $originalTenantId = app('tenant_id', null);

        // Temporariamente remove o tenant_id
        app()->instance('tenant_id', null);

        try {
            return $callback();
        } finally {
            // Restaura o tenant_id original
            if ($originalTenantId) {
                app()->instance('tenant_id', $originalTenantId);
            }
        }
    }
}

