<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    // public function register(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8|confirmed',
    //         'phone' => 'nullable|string|max:20',
    //         'company_name' => 'required|string|max:255',
    //         'company_phone' => 'required|string|max:20',
    //         'company_whatsapp' => 'required|string|max:20',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Dados inválidos',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     try {
    //         // Create company
    //         $company = Company::create([
    //             'name' => $request->company_name,
    //             'phone_1' => $request->company_phone,
    //             'has_whatsapp_1' => true,
    //             'timezone_id' => 1, // Usar timezone padrão
    //             'is_owner_company' => true, // Sempre true para proprietários no registro
    //         ]);

    //         // Buscar profile de admin
    //         $adminProfile = \App\Models\Profile::where('name', 'admin')->first();

    //         // Create user
    //         $user = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //             'phone' => $request->phone,
    //         ]);

    //         // Attach user to company with admin profile
    //         $user->companies()->attach($company->id, ['profile_id' => $adminProfile->id]);

    //         // Create token
    //         $token = $user->createToken('auth_token', $user->getAbilities())->plainTextToken;

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Usuário criado com sucesso',
    //             'data' => [
    //                 'user' => $user->load('companies'),
    //                 'company' => $company,
    //                 'token' => $token,
    //             ]
    //         ], 201);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao criar usuário',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Login user
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Carregar relacionamentos do usuário
        $user->load(['companies']);

        // Como não temos company_id específico no login, vamos usar a primeira empresa
        $firstCompany = $user->companies->first();
        $abilities = $firstCompany ? $user->getAbilities($firstCompany->id) : [];
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        // Adicionar company_ids ao response
        $userData = $user->toArray();
        $userData['company_ids'] = $user->companies->pluck('id')->toArray();

        // Garantir que as companies tenham a informação da pivot
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

        // Formatar empresas (tenants) para seleção (apenas empresas ativas)
        $tenants = $user->companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'is_main_company' => $company->pivot->is_main_company,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'token' => $token,
                'user' => $userData,
                'abilities' => $abilities,
                'tenants' => $tenants,
            ]
        ]);
    }

    /**
     * Login with Google
     */
    public function googleLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'google_id' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|email',
            'avatar' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find or create user
            $user = User::where('google_id', $request->google_id)
                      ->orWhere('email', $request->email)
                      ->first();

            if (!$user) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'google_id' => $request->google_id,
                ]);
            } else {
                // Update Google ID if not set
                if (!$user->google_id) {
                    $user->update(['google_id' => $request->google_id]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // Carregar abilities do usuário (todas as empresas)
            $user->load(['companies']);

            // Como não temos company_id específico no login, vamos usar a primeira empresa
            $firstCompany = $user->companies->first();
            $abilities = $firstCompany ? $user->getAbilities($firstCompany->id) : [];

            // Adicionar company_ids ao response
            $userData = $user->toArray();
            $userData['company_ids'] = $user->companies->pluck('id')->toArray();

            // Garantir que as companies tenham a informação da pivot
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

            // Formatar empresas (tenants) para seleção
            $tenants = $user->companies->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'is_main_company' => $company->pivot->is_main_company,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Login com Google realizado com sucesso',
                'data' => [
                    'token' => $token,
                    'user' => $userData,
                    'abilities' => $abilities,
                    'tenants' => $tenants,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer login com Google',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['companies']);

        // Como não temos company_id específico no me, vamos usar a primeira empresa
        $firstCompany = $user->companies->first();
        $abilities = $firstCompany ? $user->getAbilities($firstCompany->id) : [];

        // Adicionar company_ids ao response
        $userData = $user->toArray();
        $userData['company_ids'] = $user->companies->pluck('id')->toArray();

        // Garantir que as companies tenham a informação da pivot
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

        // Formatar empresas (tenants) para seleção (apenas empresas ativas)
        $tenants = $user->companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'is_main_company' => $company->pivot->is_main_company,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'abilities' => $abilities,
                'tenants' => $tenants,
            ]
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Carregar abilities do usuário
        $user->load('profile.abilities');
        $abilities = $user->getAbilities();

        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token renovado com sucesso',
            'data' => [
                'user' => $user->load('profile.abilities'),
                'token' => $token,
                'abilities' => $abilities,
            ]
        ]);
    }

    /**
     * Alterar senha do usuário autenticado
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        // Verificar se a senha atual está correta
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha atual incorreta'
            ], 422);
        }

        // Atualizar a senha
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ]);
    }
}
