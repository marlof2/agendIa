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

            if (empty($companyIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selecione pelo menos uma empresa'
                ], 422);
            }

            // Se profile_id foi fornecido, usar attach com dados da pivot
            if ($profileId) {
                // Verificar se o perfil existe
                $profile = \App\Models\Profile::find($profileId);
                if (!$profile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Perfil não encontrado'
                    ], 422);
                }

                // Associar usuário às empresas com o perfil especificado
                foreach ($companyIds as $companyId) {
                    // Verificar se já está associado
                    if (!$user->companies()->where('companies.id', $companyId)->exists()) {
                        $user->companies()->attach($companyId, [
                            'profile_id' => $profileId,
                            'is_main_company' => false // Não é principal quando associado via perfil
                        ]);
                    }
                }

                // Recarregar relacionamentos com pivot
                $user->load(['companies' => function ($query) {
                    $query->withPivot('profile_id', 'is_main_company');
                }]);

                return response()->json([
                    'success' => true,
                    'message' => 'Associação realizada com sucesso!',
                    'data' => [
                        'companies' => $user->companies->map(function ($company) {
                            return [
                                'id' => $company->id,
                                'name' => $company->name,
                                'pivot' => [
                                    'profile_id' => $company->pivot->profile_id,
                                    'is_main_company' => $company->pivot->is_main_company
                                ]
                            ];
                        })
                    ]
                ]);
            } else {
                // Comportamento original: sincronizar sem dados da pivot
                $user->companies()->syncWithoutDetaching($companyIds);

                // Recarregar relacionamentos
                $user->load('companies');

                return response()->json([
                    'success' => true,
                    'message' => 'Associação realizada com sucesso!',
                    'data' => [
                        'companies' => $user->companies->map(function ($company) {
                            return [
                                'id' => $company->id,
                                'name' => $company->name,
                            ];
                        })
                    ]
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao associar empresas: ' . $e->getMessage()
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
     * Desvincular uma empresa específica do usuário autenticado
     */
    public function detachCompany(Request $request, int $companyId): JsonResponse
    {
        try {
            $user = $request->user();

            // Verificar se o usuário tem pelo menos 2 empresas
            if ($user->companies()->count() <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você precisa estar vinculado a pelo menos uma empresa'
                ], 422);
            }

            // Verificar se está vinculado à empresa
            if (!$user->companies()->where('company_id', $companyId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não está vinculado a esta empresa'
                ], 422);
            }

            // Desvincular
            $user->companies()->detach($companyId);
            $user->load('companies');

            return response()->json([
                'success' => true,
                'message' => 'Empresa desvinculada com sucesso!',
                'data' => [
                    'companies' => $user->companies->map(function ($company) {
                        return [
                            'id' => $company->id,
                            'name' => $company->name,
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desvincular empresa: ' . $e->getMessage()
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

            // Verificar se o usuário está vinculado a essa empresa
            $company = $user->companies()->where('companies.id', $companyId)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não está vinculado a esta empresa'
                ], 422);
            }

            // Se está marcando como principal, desmarcar todas as outras
            if ($isMain) {
                $user->companies()->updateExistingPivot(
                    $user->companies->pluck('id')->toArray(),
                    ['is_main_company' => false]
                );
            }

            // Atualizar a empresa específica
            $user->companies()->updateExistingPivot($companyId, [
                'is_main_company' => $isMain
            ]);

            // Recarregar dados do usuário
            $user->load(['companies']);

            // Formatar response
            $userData = $user->toArray();
            $userData['companies'] = $user->companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'pivot' => [
                        'profile_id' => $company->pivot->profile_id,
                        'is_main_company' => $company->pivot->is_main_company,
                    ]
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'message' => $isMain ? 'Empresa definida como principal' : 'Empresa removida como principal',
                'data' => [
                    'user' => $userData
                ]
            ]);

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
