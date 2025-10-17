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
     * Associar usuário autenticado a empresas
     */
    public function associateCompanies(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyIds = $request->input('company_ids', []);
            $profileId = $request->input('profile_id');

            $updatedUser = $this->userService->associateUserToCompanies($user, $companyIds, $profileId);

            return response()->json([
                'success' => true,
                'message' => 'Associação realizada com sucesso!',
                'data' => [
                    'companies' => $this->userService->formatUserCompanies($updatedUser)
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao associar usuário a empresas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
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
     * Desvincular uma empresa específica do usuário autenticado
     */
    public function detachCompany(Request $request, int $companyId): JsonResponse
    {
        try {
            $user = $request->user();
            $updatedUser = $this->userService->disassociateUserFromCompany($user, $companyId);

            return response()->json([
                'success' => true,
                'message' => 'Empresa desvinculada com sucesso!',
                'data' => [
                    'companies' => $this->userService->formatUserCompanies($updatedUser)
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao desvincular empresa:', [
                'user_id' => $request->user()->id,
                'company_id' => $companyId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao desvincular empresa'
            ], 500);
        }
    }

    /**
     * Update main company for user
     */
    public function updateMainCompany(Request $request): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'is_main' => 'required|boolean'
        ]);

        try {
            $user = $request->user();
            $companyId = $request->company_id;
            $isMain = $request->is_main;

            if ($isMain) {
                $updatedUser = $this->userService->setMainCompany($user, $companyId);
                $message = 'Empresa definida como principal';
            } else {
                $updatedUser = $this->userService->removeMainCompany($user, $companyId);
                $message = 'Empresa removida como principal';
            }

            // Formatar response
            $userData = $updatedUser->toArray();
            $userData['companies'] = $this->userService->formatUserCompanies($updatedUser);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user' => $userData
                ]
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar empresa principal:', [
                'user_id' => $request->user()->id,
                'company_id' => $request->company_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar empresa principal'
            ], 500);
        }
    }

}
