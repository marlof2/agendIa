<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    /**
     * Listar clientes da empresa atual
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->tenant_id; // Vem do middleware TenantScope
        $search = $request->get('search');
        $perPage = $request->get('per_page', 12);

        // Buscar perfil de cliente
        $clientProfile = Profile::where('name', 'client')->first();

        if (!$clientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Perfil de cliente não encontrado'
            ], 404);
        }

        // Query base: usuários com perfil de cliente
        $query = User::with(['profile', 'companies'])
                     ->where('profile_id', $clientProfile->id);

        // Filtrar por empresa
        if ($tenantId) {
            $query->whereHas('companies', function ($q) use ($tenantId) {
                $q->where('companies.id', $tenantId);
            });
        }

        // Busca
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate($perPage);

        return response()->json($clients);
    }

    /**
     * Mostrar um cliente específico
     */
    public function show(Request $request, User $user): JsonResponse
    {
        $tenantId = $request->tenant_id;

        // Verifica se o usuário é cliente
        if ($user->profile->name !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não é um cliente'
            ], 403);
        }

        // Verifica se o cliente pertence à empresa atual
        if ($tenantId && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não pertence a esta empresa'
            ], 403);
        }

        $user->load(['profile', 'companies']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Criar novo cliente
     */
    public function store(Request $request): JsonResponse
    {
        $tenantId = $request->tenant_id;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Buscar perfil de cliente
            $clientProfile = Profile::where('name', 'client')->first();

            // Criar usuário
            $client = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'profile_id' => $clientProfile->id,
            ]);

            // Vincular à empresa atual
            if ($tenantId) {
                $client->companies()->attach($tenantId);
            }

            $client->load(['profile', 'companies']);

            return response()->json([
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'data' => $client
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar cliente
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $tenantId = $request->tenant_id;

        // Verifica se é cliente
        if ($user->profile->name !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não é um cliente'
            ], 403);
        }

        // Verifica acesso
        if ($tenantId && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não pertence a esta empresa'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'password' => 'sometimes|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only(['name', 'email', 'phone']);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);
            $user->load(['profile', 'companies']);

            return response()->json([
                'success' => true,
                'message' => 'Cliente atualizado com sucesso',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar cliente
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $tenantId = $request->tenant_id;

        // Verifica se é cliente
        if ($user->profile->name !== 'client') {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não é um cliente'
            ], 403);
        }

        // Verifica acesso
        if ($tenantId && !$user->companies()->where('companies.id', $tenantId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente não pertence a esta empresa'
            ], 403);
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cliente deletado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar cliente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

