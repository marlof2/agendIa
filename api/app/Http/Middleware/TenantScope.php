<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pega o tenant_id do header
        $tenantId = $request->header('X-Tenant-ID');

        // Se não houver tenant_id no header, tenta pegar do usuário autenticado
        if (!$tenantId && $request->user()) {
            // Pega a primeira empresa do usuário como fallback
            $tenantId = $request->user()->companies()->first()?->id;
        }

        // Armazena o tenant_id na requisição para uso posterior
        if ($tenantId) {
            $request->merge(['tenant_id' => $tenantId]);

            // Define o tenant_id globalmente para ser usado em qualquer lugar
            app()->instance('tenant_id', (int) $tenantId);
        }

        return $next($request);
    }
}
