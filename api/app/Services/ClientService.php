<?php

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use App\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Hash;

class ClientService
{
    /**
     * Get all clients with pagination and search
     */
    public function getAllClients(array $filters = []): LengthAwarePaginator
    {
        $tenantId = $filters['tenant_id'] ?? null;

        // Buscar perfil de cliente
        $clientProfile = Profile::where('name', 'client')->first();

        if (!$clientProfile) {
            throw new \Exception('Perfil de cliente não encontrado');
        }

        $query = User::with(['companies']);

        // Filtrar por empresa e perfil
        if ($tenantId) {
            // Se tem tenant_id, buscar apenas clientes dessa empresa específica
            $query->whereHas('companies', function ($q) use ($tenantId, $clientProfile) {
                $q->where('companies.id', $tenantId)
                  ->where('company_user.profile_id', $clientProfile->id);
            });
        }

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 12;

        return $query->paginate($perPage);
    }

    /**
     * Get a specific client by ID
     */
    public function getClientById(int $id, int $tenantId = null): User
    {
        $client = User::with(['companies'])->findOrFail($id);

        // Buscar perfil de cliente
        $clientProfile = Profile::where('name', 'client')->first();

        if (!$clientProfile) {
            throw new \Exception('Perfil de cliente não encontrado');
        }

        // Verifica se é cliente na empresa específica
        if ($tenantId) {
            $profile = $client->getProfileForCompany($tenantId);
            if (!$profile || $profile->name !== 'client') {
                throw new \Exception('Usuário não é um cliente nesta empresa');
            }

            // Verifica acesso à empresa
            if (!$client->companies()->where('companies.id', $tenantId)->exists()) {
                throw new \Exception('Cliente não pertence a esta empresa');
            }
        } else {
            // Se não especificou empresa, verifica se é cliente em alguma empresa
            $isClient = $client->companies()->wherePivot('profile_id', $clientProfile->id)->exists();
            if (!$isClient) {
                throw new \Exception('Usuário não é um cliente');
            }
        }

        return $client;
    }

    /**
     * Create a new client
     */
    public function createClient(array $data): User
    {
        $tenantId = $data['tenant_id'] ?? null;
        unset($data['tenant_id']);

        // Buscar perfil de cliente
        $clientProfile = Profile::where('name', 'client')->first();

        if (!$clientProfile) {
            throw new \Exception('Perfil de cliente não encontrado');
        }

        $cleanedData = $this->cleanClientData($data);

        // Hash password if provided
        if (isset($cleanedData['password']) && $cleanedData['password']) {
            $cleanedData['password'] = Hash::make($cleanedData['password']);
        }

        $client = User::create($cleanedData);

        // Vincular à empresa atual com o perfil de cliente
        if ($tenantId) {
            $client->companies()->attach($tenantId, ['profile_id' => $clientProfile->id]);
        }

        return $client->load(['companies']);
    }

    /**
     * Update an existing client
     */
    public function updateClient(User $client, array $data): User
    {
        $tenantId = $data['tenant_id'] ?? null;
        unset($data['tenant_id']);

        // Verifica se é cliente na empresa específica
        if ($tenantId) {
            $profile = $client->getProfileForCompany($tenantId);
            if (!$profile || $profile->name !== 'client') {
                throw new \Exception('Usuário não é um cliente nesta empresa');
            }

            // Verifica acesso à empresa
            if (!$client->companies()->where('companies.id', $tenantId)->exists()) {
                throw new \Exception('Cliente não pertence a esta empresa');
            }
        }

        $cleanedData = $this->cleanClientData($data);

        // Hash password if provided
        if (isset($cleanedData['password']) && $cleanedData['password']) {
            $cleanedData['password'] = Hash::make($cleanedData['password']);
        } else {
            // Remove password from update if not provided
            unset($cleanedData['password']);
        }

        $client->update($cleanedData);

        return $client->load(['companies']);
    }

    /**
     * Delete a client
     */
    public function deleteClient(User $client, int $tenantId = null): bool
    {
        // Verifica se é cliente na empresa específica
        if ($tenantId) {
            $profile = $client->getProfileForCompany($tenantId);
            if (!$profile || $profile->name !== 'client') {
                throw new \Exception('Usuário não é um cliente nesta empresa');
            }

            // Verifica acesso à empresa
            if (!$client->companies()->where('companies.id', $tenantId)->exists()) {
                throw new \Exception('Cliente não pertence a esta empresa');
            }

            // Remove apenas da empresa específica
            $client->companies()->detach($tenantId);
        } else {
            // Remove de todas as empresas
            $client->companies()->detach();
        }

        return $client->delete();
    }

    /**
     * Clean client data by removing masks
     */
    private function cleanClientData(array $data): array
    {
        $cleaned = $data;

        if (isset($cleaned['phone']) && $cleaned['phone']) {
            $cleaned['phone'] = $this->cleanMask($cleaned['phone']);
        }

        if (isset($cleaned['cpf']) && $cleaned['cpf']) {
            $cleaned['cpf'] = $this->cleanMask($cleaned['cpf']);
        }

        return $cleaned;
    }

    /**
     * Clean mask from string
     */
    private function cleanMask(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Export clients to Excel
     */
    public function exportToExcel(array $filters = []): BinaryFileResponse
    {
        $clients = $this->getAllClients($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Telefone'],
            ['key' => 'cpf', 'label' => 'CPF'],
            ['key' => 'has_whatsapp', 'label' => 'WhatsApp'],
            ['key' => 'companies_names', 'label' => 'Empresas'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $clients->map(function ($client) {
            // Formatar telefone
            $phone = $client->phone ? $this->formatPhone($client->phone) : '';

            // Formatar CPF
            $cpf = $client->cpf ? $this->formatCPF($client->cpf) : '';

            // Nomes das empresas
            $companiesNames = $client->companies->pluck('name')->join(', ');

            return [
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $phone,
                'cpf' => $cpf,
                'has_whatsapp' => $client->has_whatsapp ? 'Sim' : 'Não',
                'companies_names' => $companiesNames,
                'created_at' => $client->created_at->format('d/m/Y H:i'),
            ];
        });

        return ExportFactory::exportToExcel($data->toArray(), $headers, 'clientes', 'Relatório de Clientes');
    }

    /**
     * Export clients to PDF
     */
    public function exportToPDF(array $filters = []): Response
    {
        $clients = $this->getAllClients($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Telefone'],
            ['key' => 'cpf', 'label' => 'CPF'],
            ['key' => 'has_whatsapp', 'label' => 'WhatsApp'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $clients->map(function ($client) {
            // Formatar telefone
            $phone = $client->phone ? $this->formatPhone($client->phone) : '';

            // Formatar CPF
            $cpf = $client->cpf ? $this->formatCPF($client->cpf) : '';

            return [
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $phone,
                'cpf' => $cpf,
                'has_whatsapp' => $client->has_whatsapp ? 'Sim' : 'Não',
                'created_at' => $client->created_at->format('d/m/Y H:i'),
            ];
        });

        return ExportFactory::exportToPDF($data->toArray(), $headers, 'clientes', 'Relatório de Clientes');
    }

    /**
     * Format phone for display
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $phone);
        } elseif (strlen($phone) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $phone);
        }

        return $phone;
    }

    /**
     * Format CPF for display
     */
    private function formatCPF(string $cpf): string
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf);
    }
}
