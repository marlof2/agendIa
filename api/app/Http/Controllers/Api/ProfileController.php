<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Ability;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Get all profiles
     */
    public function index(Request $request): JsonResponse
    {
        $query = Profile::with('abilities');

        // Filter by search term
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $profiles = $query->paginate($perPage);

        return response()->json($profiles);
    }

    /**
     * Get a specific profile
     */
    public function show(Profile $profile): JsonResponse
    {
        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * Create a new profile
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:profiles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'abilities' => 'array',
            'abilities.*' => 'exists:abilities,id'
        ]);

        $profile = Profile::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->has('abilities')) {
            $profile->abilities()->attach($request->abilities);
        }

        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'message' => 'Perfil criado com sucesso',
            'data' => $profile
        ], 201);
    }

    /**
     * Update a profile
     */
    public function update(Request $request, Profile $profile): JsonResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('profiles', 'name')->ignore($profile->id)
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'abilities' => 'array',
            'abilities.*' => 'exists:abilities,id'
        ]);

        $profile->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->has('abilities')) {
            $profile->abilities()->sync($request->abilities);
        }

        $profile->load('abilities');

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
        // Verificar se o perfil está sendo usado por algum usuário
        if ($profile->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar um perfil que está sendo usado por usuários'
            ], 422);
        }

        $profile->abilities()->detach();
        $profile->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perfil deletado com sucesso'
        ]);
    }

    /**
     * Get all abilities grouped by category
     */
    public function abilities(): JsonResponse
    {
        $abilities = Ability::active()->get()->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => $abilities
        ]);
    }

    /**
     * Update profile abilities
     */
    public function updateAbilities(Request $request, Profile $profile): JsonResponse
    {
        $request->validate([
            'abilities' => 'required|array',
            'abilities.*' => 'exists:abilities,id'
        ]);

        $profile->abilities()->sync($request->abilities);
        $profile->load('abilities');

        return response()->json([
            'success' => true,
            'message' => 'Permissões do perfil atualizadas com sucesso',
            'data' => $profile
        ]);
    }
}
