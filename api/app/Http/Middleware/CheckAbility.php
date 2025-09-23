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



        if (!$auth->user()->hasAbility($ability)) {
            return response()->json([
                'message' => 'Insufficient abiliies.',
                'error' => 'INSUFFICIENT_ABILITIES',
                'required_ability' => $ability,
                // 'user_abilities' => $user->getAbilities()
            ], 403);
        }

        return $next($request);
    }
}
