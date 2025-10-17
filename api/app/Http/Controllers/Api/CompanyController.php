<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Http\Requests\ExportRequest;
use App\Services\CompanyService;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyService $companyService
    ) {}

    /**
     * Buscar todas as empresas
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 12),
            'status' => $request->get('status', 'active') // active, inactive, all
        ];

        // Passa o usuário autenticado para filtrar por acesso
        $companies = $this->companyService->getAllCompanies($filters, $request->user());

        return response()->json($companies);
    }

    /**
     * Buscar uma empresa específica
     */
    public function show(Company $company): JsonResponse
    {
        $company = $this->companyService->getCompanyById($company->id);

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Criar uma nova empresa
     */
    public function store(CompanyRequest $request): JsonResponse
    {
        $company = $this->companyService->createCompany($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Empresa criada com sucesso',
            'data' => $company
        ], 201);
    }

    /**
     * Atualizar uma empresa
     */
    public function update(CompanyRequest $request, Company $company): JsonResponse
    {
        $company = $this->companyService->updateCompany($company, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Empresa atualizada com sucesso',
            'data' => $company
        ]);
    }

    /**
     * Deletar uma empresa
     */
    public function destroy(Company $company): JsonResponse
    {
        try {
            $this->companyService->deleteCompany($company);

            return response()->json([
                'success' => true,
                'message' => 'Empresa deletada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Inativar uma empresa (soft delete)
     */
    public function deactivate(Company $company): JsonResponse
    {
        try {
            $company->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Empresa inativada com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao inativar empresa: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ativar uma empresa (restore)
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $company = Company::withTrashed()->findOrFail($id);
            $company->restore();

            return response()->json([
                'success' => true,
                'message' => 'Empresa ativada com sucesso',
                'data' => $company
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ativar empresa: ' . $e->getMessage()
            ], 422);
        }
    }


    /**
     * Exportar empresas para Excel ou PDF
     */
    public function export(ExportRequest $request)
    {
        $filters = [
            'search' => $request->get('search')
        ];

        if ($request->format === 'excel') {
            return $this->companyService->exportToExcel($filters, $request->user());
        } else {
            return $this->companyService->exportToPDF($filters, $request->user());
        }
    }

    /**
     * Buscar empresas para combos/autoseleção
     */
    public function combo(Request $request): JsonResponse
    {

        $companies = Company::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $companies,
            'total' => $companies->count()
        ]);
    }


    /**
     * Associar um usuário a uma empresa com perfil específico
     */
    public function attachUserToCompany(Request $request, $companyId): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'profile_id' => 'required|integer|exists:profiles,id'
        ]);

        try {
            $result = $this->companyService->attachUserToCompany(
                (int)$companyId,
                (int)$request->user_id,
                (int)$request->profile_id
            );

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Alterar perfil de um usuário em uma empresa específica
     */
    public function updateUserProfileInCompany(Request $request, $companyId, $userId): JsonResponse
    {
        $request->validate([
            'profile_id' => 'required|integer|exists:profiles,id'
        ]);

        try {
            $result = $this->companyService->updateUserProfileInCompany(
                (int)$companyId,
                (int)$userId,
                (int)$request->profile_id
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Desassociar um usuário de uma empresa
     */
    public function detachUserFromCompany(Request $request, $companyId, $userId): JsonResponse
    {
        try {
            $result = $this->companyService->detachUserFromCompany((int)$companyId, (int)$userId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Buscar usuários disponíveis para associação a uma empresa
     */
    public function availableUsersForCompany(Request $request, $companyId): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'per_page' => $request->get('per_page', 15),
            ];

            $result = $this->companyService->getAvailableUsersForCompany((int)$companyId, $filters);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuários disponíveis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar usuários de uma empresa específica
     */
    public function companyUsers(Request $request, $companyId): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'profile_id' => $request->get('profile_id'),
                'per_page' => $request->get('per_page', 12),
            ];

            $result = $this->companyService->getCompanyUsers((int)$companyId, $filters);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuários da empresa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar profissionais vinculados à empresa para Excel ou PDF
     */
    public function exportBindProfessional(ExportRequest $request, $companyId)
    {
        $filters = [
            'search' => $request->get('search')
        ];

        if ($request->format === 'excel') {
            return $this->companyService->exportProfessionalsToExcel((int)$companyId, $filters);
        } else {
            return $this->companyService->exportProfessionalsToPDF((int)$companyId, $filters);
        }
    }

    /**
     * Listar empresas disponíveis para associação (usuário autenticado)
     */
    public function availableForAssociation(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 12);

        $query = Company::select('id', 'name', 'person_type', 'responsible_name', 'phone_1')
            ->orderBy('name')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('responsible_name', 'like', "%{$search}%");
                });
            });
        // Busca

        $companies = $query->paginate($perPage);

        return response()->json($companies);
    }
}
