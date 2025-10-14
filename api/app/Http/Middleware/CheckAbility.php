<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $auth = auth();

        if (!$auth->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }



        $user = $auth->user();
        $tenantId = $request->get('tenant_id') ?? app('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'message' => 'Tenant ID não fornecido.',
                'error' => 'MISSING_TENANT_ID',
            ], 400);
        }

        if (!$user->hasPermission($ability, $tenantId)) {
            return response()->json([
                'message' => 'Sem permissão para acessar este recurso.',
                'error' => 'INSUFFICIENT_ABILITIES',
                'required_ability' => $ability,
            ], 403);
        }

        return $next($request);
    }
}
