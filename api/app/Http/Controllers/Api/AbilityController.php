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
     * Get user abilities for a specific company
     */
    public function userAbilities(Request $request, User $user): JsonResponse
    {
        $companyId = $request->get('company_id');

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'company_id é obrigatório'
            ], 400);
        }

        $profile = $user->getProfileForCompany($companyId);
        $user->load('companies');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'profile' => $profile,
                'abilities' => $user->getAbilities($companyId),
                'abilities_grouped' => $user->getAbilitiesGrouped($companyId)
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
