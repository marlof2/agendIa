<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AbilityController extends Controller
{
    /**
     * Get all available abilities
     */
    public function index(): JsonResponse
    {
        $abilities = Ability::get();

        return response()->json([
            'success' => true,
            'data' => $abilities
        ]);
    }

    /**
     * Get user abilities
     */
    public function userAbilities(User $user): JsonResponse
    {
        $user->load('profile.abilities');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $user->profile,
                'abilities' => $user->getAbilities(),
                'abilities_grouped' => $user->getAbilitiesGrouped()
            ]
        ]);
    }



    /**
     * Get abilities by profile
     */
    public function abilitiesByProfile(string $profileName): JsonResponse
    {
        $profile = Profile::where('name', $profileName)->with('abilities')->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile,
                'abilities' => $profile->abilities->pluck('name')->toArray(),
                'abilities_grouped' => $profile->getAbilitiesGrouped()
            ]
        ]);
    }
}
