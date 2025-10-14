<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientService;
use App\Http\Requests\ClientRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    /**
     * Listar clientes da empresa atual
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'tenant_id' => $request->tenant_id,
                'per_page' => $request->get('per_page', 12),
            ];

            $clients = $this->clientService->getAllClients($filters);

            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar um cliente específico
     */
    public function show(Request $request, User $user): JsonResponse
    {
        try {
            $client = $this->clientService->getClientById($user->id, $request->tenant_id);

            return response()->json([
                'success' => true,
                'data' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }

    /**
     * Criar novo cliente
     */
    public function store(ClientRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = $request->tenant_id;

            $client = $this->clientService->createClient($data);

            return response()->json([
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'data' => $client
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar cliente
     */
    public function update(ClientRequest $request, User $user): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = $request->tenant_id;

            $client = $this->clientService->updateClient($user, $data);

            return response()->json([
                'success' => true,
                'message' => 'Cliente atualizado com sucesso',
                'data' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deletar cliente
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        try {
            $this->clientService->deleteClient($user, $request->tenant_id);

            return response()->json([
                'success' => true,
                'message' => 'Cliente deletado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export clients to Excel or PDF
     */
    public function export(Request $request)
    {
        try {
            $filters = [
                'search' => $request->get('search'),
                'tenant_id' => $request->tenant_id,
                'format' => $request->get('format', 'xlsx'),
            ];

            if ($filters['format'] === 'pdf') {
                $file = $this->clientService->exportToPDF($filters);
                return $file;
            } else {
                $file = $this->clientService->exportToExcel($filters);
                return $file;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar clientes: ' . $e->getMessage()
            ], 500);
        }
    }
}

