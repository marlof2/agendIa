<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * List all active companies
     */
    public function index(): JsonResponse
    {
        $companies = Company::with(['users'])->get();

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Show a specific company
     */
    public function show(Company $company): JsonResponse
    {
        $company->load(['users', 'services', 'appointments']);

        return response()->json([
            'success' => true,
            'data' => $company
        ]);
    }

    /**
     * Deactivate a company (soft delete)
     */
    public function deactivate(Company $company): JsonResponse
    {
        $company->deactivate();

        return response()->json([
            'success' => true,
            'message' => 'Empresa desativada com sucesso'
        ]);
    }

    /**
     * Activate a company (restore)
     */
    public function activate($id): JsonResponse
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->activate();

        return response()->json([
            'success' => true,
            'message' => 'Empresa ativada com sucesso'
        ]);
    }

    /**
     * List all companies (including soft deleted)
     */
    public function all(): JsonResponse
    {
        $companies = Company::withTrashed()->with(['users'])->get();

        return response()->json([
            'success' => true,
            'data' => $companies
        ]);
    }

    /**
     * Permanently delete a company
     */
    public function destroy(Company $company): JsonResponse
    {
        $company->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Empresa excluída permanentemente'
        ]);
    }
}
