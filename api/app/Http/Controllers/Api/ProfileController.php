<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\UpdateAbilitiesRequest;
use App\Http\Requests\ExportRequest;
use App\Services\ProfileService;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService
    ) {}

    /**
     * Get all profiles
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 12)
        ];

        $profiles = $this->profileService->getAllProfiles($filters);

        return response()->json($profiles);
    }

    /**
     * Get a specific profile
     */
    public function show(Profile $profile): JsonResponse
    {
        $profile = $this->profileService->getProfileById($profile->id);

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * Create a new profile
     */
    public function store(ProfileRequest $request): JsonResponse
    {
        $profile = $this->profileService->createProfile($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Perfil criado com sucesso',
            'data' => $profile
        ], 201);
    }

    /**
     * Update a profile
     */
    public function update(ProfileRequest $request, Profile $profile): JsonResponse
    {
        $profile = $this->profileService->updateProfile($profile, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Perfil atualizado com sucesso',
            'data' => $profile
        ]);
    }

    /**
     * Delete a profile
     */
    public function destroy(Profile $profile): JsonResponse
    {
        try {
            $this->profileService->deleteProfile($profile);

            return response()->json([
                'success' => true,
                'message' => 'Perfil deletado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get all abilities
     */
    public function abilities(): JsonResponse
    {
        $abilities = $this->profileService->getAllAbilities();

        return response()->json([
            'success' => true,
            'data' => $abilities
        ]);
    }

    /**
     * Update profile abilities
     */
    public function updateAbilities(UpdateAbilitiesRequest $request, Profile $profile): JsonResponse
    {
        $profile = $this->profileService->updateProfileAbilities($profile, $request->validated()['abilities']);

        return response()->json([
            'success' => true,
            'message' => 'Permissões do perfil atualizadas com sucesso',
            'data' => $profile
        ]);
    }

    /**
     * Export profiles to Excel or PDF
     */
    public function export(ExportRequest $request)
    {
        $filters = [
            'search' => $request->get('search')
        ];

        if ($request->format === 'excel') {
            return $this->profileService->exportToExcel($filters);
        } else {
            return $this->profileService->exportToPDF($filters);
        }
    }

}
