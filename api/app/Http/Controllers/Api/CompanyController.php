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
     * Get all companies
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->get('search'),
            'per_page' => $request->get('per_page', 12)
        ];

        $companies = $this->companyService->getAllCompanies($filters);

        return response()->json($companies);
    }

    /**
     * Get a specific company
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
     * Create a new company
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
     * Update a company
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
     * Delete a company
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
     * Export companies to Excel or PDF
     */
    public function export(ExportRequest $request)
    {
        $filters = [
            'search' => $request->get('search')
        ];

        if ($request->format === 'excel') {
            return $this->companyService->exportToExcel($filters);
        } else {
            return $this->companyService->exportToPDF($filters);
        }
    }
}
