<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'profile_id' => $request->get('profile_id'),
                'per_page' => $request->get('per_page', 12),
            ];

            $users = $this->userService->getAllUsers($filters);

            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user): JsonResponse
    {
        try {
            $userData = $this->userService->getUserById($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Usuário encontrado com sucesso',
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $updatedUser
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users to Excel or PDF
     */
    public function export(Request $request)
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'profile_id' => $request->get('profile_id'),
                'format' => $request->get('format', 'xlsx'),
            ];

            if ($filters['format'] === 'pdf') {
                $file = $this->userService->exportToPDF($filters);
                return $file;
            } else {
                $file = $this->userService->exportToExcel($filters);
                return $file;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar usuários: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar profissionais disponíveis para associação a uma empresa
     */
    public function availableProfessionals(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'company_id' => $request->get('company_id'),
                'per_page' => $request->get('per_page', 12),
            ];

            // Buscar profissionais usando o service com paginação
            $result = $this->userService->getAvailableProfessionals($filters);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar profissionais disponíveis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adicionar usuário a uma empresa com perfil específico
     */
    public function addToCompany(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'company_id' => 'required|exists:companies,id',
                'profile_id' => 'required|exists:profiles,id',
            ]);

            $user = User::findOrFail($request->user_id);

            $this->userService->addUserToCompany(
                $user,
                $request->company_id,
                $request->profile_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Usuário adicionado à empresa com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar usuário à empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover usuário de uma empresa
     */
    public function removeFromCompany(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'company_id' => 'required|exists:companies,id',
            ]);

            $user = User::findOrFail($request->user_id);

            $this->userService->removeUserFromCompany($user, $request->company_id);

            return response()->json([
                'success' => true,
                'message' => 'Usuário removido da empresa com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover usuário da empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar perfil do usuário em uma empresa específica
     */
    public function updateProfileInCompany(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'company_id' => 'required|exists:companies,id',
                'profile_id' => 'required|exists:profiles,id',
            ]);

            $user = User::findOrFail($request->user_id);

            $this->userService->updateUserProfileInCompany(
                $user,
                $request->company_id,
                $request->profile_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Perfil do usuário atualizado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil do usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter empresas do usuário com seus perfis
     */
    public function getUserCompanies(User $user): JsonResponse
    {
        try {
            $companies = $this->userService->getUserCompaniesWithProfiles($user);

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar empresas do usuário: ' . $e->getMessage()
            ], 500);
        }
    }

}
