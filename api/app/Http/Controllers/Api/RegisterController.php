<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Criar usuário
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'cpf' => $request->cpf,
                'has_whatsapp' => $request->has_whatsapp ?? false,
            ]);

            // Se for proprietário, criar empresa
            if ($request->account_type === 'owner') {
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

                // Associar usuário à empresa criada com o perfil de owner e marcar como empresa principal
                $user->companies()->attach($company->id, [
                    'profile_id' => $request->profile_id,
                    'is_main_company' => true
                ]);
            }
            // Para outros perfis, a associação será feita após o login

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


}

