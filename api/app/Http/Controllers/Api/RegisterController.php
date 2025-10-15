<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\RegisterCompanyRequest;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Register a new user - Basic registration only
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Criar usuário apenas com dados básicos
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'cpf' => $request->cpf,
                'has_whatsapp' => $request->has_whatsapp ?? false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conta criada com sucesso! Faça login para acessar o sistema.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao registrar usuário:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar conta: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register company for owner
     */
    public function registerCompany(RegisterCompanyRequest $request): JsonResponse
    {

        try {
            DB::beginTransaction();

            // Verificar se o usuário já tem uma empresa
            $user = User::findOrFail($request->user_id);
            if ($user->companies()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário já possui uma empresa cadastrada'
                ], 400);
            }

            // Criar empresa
            $company = Company::create([
                'name' => $request->input('company.name'),
                'person_type' => $request->input('company.person_type'),
                'cnpj' => $request->input('company.cnpj'),
                'cpf' => $request->input('company.cpf'),
                'responsible_name' => $request->input('company.responsible_name'),
                'phone_1' => $request->input('company.phone_1'),
                'has_whatsapp_1' => $request->input('company.has_whatsapp_1', false),
                'phone_2' => $request->input('company.phone_2'),
                'has_whatsapp_2' => $request->input('company.has_whatsapp_2', false),
                'timezone_id' => $request->input('company.timezone_id'),
            ]);

            // Buscar perfil de proprietário
            $ownerProfile = \App\Models\Profile::where('name', 'owner')->first();
            if (!$ownerProfile) {
                throw new \Exception('Perfil de proprietário não encontrado');
            }

            // Associar usuário à empresa como proprietário e marcar como empresa principal
            $user->companies()->attach($company->id, [
                'profile_id' => $ownerProfile->id,
                'is_main_company' => true
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Empresa cadastrada com sucesso!',
                'data' => [
                    'company' => $company,
                    'user' => $user,
                    'profile_id' => $ownerProfile->id,
                    'profile_name' => $ownerProfile->display_name
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao cadastrar empresa:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar empresa: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Associate user with company and profile
     */
    public function associateWithCompany(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'profile_id' => 'required|exists:profiles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $company = Company::findOrFail($request->company_id);
            $profile = \App\Models\Profile::findOrFail($request->profile_id);

            // Verificar se o usuário já está associado à empresa
            if ($user->companies()->where('companies.id', $company->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário já está associado a esta empresa'
                ], 400);
            }

            // VALIDAÇÃO DE SEGURANÇA: Verificar se é proprietário
            if ($profile->name === 'owner') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível associar como proprietário. Use o cadastro de empresa para proprietários.'
                ], 403);
            }

            // Associar usuário à empresa com o perfil especificado
            $user->companies()->attach($company->id, [
                'profile_id' => $profile->id,
                'is_main_company' => !$user->companies()->exists() // Primeira empresa é principal
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuário associado à empresa com sucesso!',
                'data' => [
                    'user' => $user,
                    'company' => $company,
                    'profile' => $profile
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao associar usuário à empresa:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao associar usuário: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

