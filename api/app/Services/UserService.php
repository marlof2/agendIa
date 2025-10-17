<?php

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use App\Models\Company;
use App\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Get all users with pagination and search
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['companies']);

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by profile (now through company_user pivot)
        if (!empty($filters['profile_id'])) {
            $query->whereHas('companies', function ($q) use ($filters) {
                $q->wherePivot('profile_id', $filters['profile_id']);
            });
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 12;

        return $query->paginate($perPage);
    }

    /**
     * Get a specific user by ID
     */
    public function getUserById(int $id): User
    {
        return User::with(['companies'])->findOrFail($id);
    }

    /**
     * Clean mask from field value
     */
    private function cleanMask(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Clean user data by removing masks
     */
    private function cleanUserData(array $data): array
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
     * Create a new user
     */
    public function createUser(array $data): User
    {
        $cleanedData = $this->cleanUserData($data);

        // Hash password if provided
        if (isset($cleanedData['password']) && $cleanedData['password']) {
            $cleanedData['password'] = Hash::make($cleanedData['password']);
        }

        $user = User::create($cleanedData);

        // Attach to companies if provided
        if (isset($cleanedData['company_ids']) && is_array($cleanedData['company_ids'])) {
            $user->companies()->attach($cleanedData['company_ids']);
        }

        return $user->load(['companies']);
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $data): User
    {
        $cleanedData = $this->cleanUserData($data);
        // Hash password if provided
        if (isset($cleanedData['password']) && $cleanedData['password']) {
            $cleanedData['password'] = Hash::make($cleanedData['password']);
        } else {
            // Remove password from update if not provided
            unset($cleanedData['password']);
        }

        $user->update($cleanedData);

        return $user->load(['companies']);
    }

    /**
     * Delete a user (soft delete)
     */
    public function deleteUser(User $user): bool
    {
        // Remove from all companies
        $user->companies()->detach();

        return $user->delete();
    }

    /**
     * Export users to Excel
     */
    public function exportToExcel(array $filters = []): BinaryFileResponse
    {
        $users = $this->getAllUsers($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Telefone'],
            ['key' => 'profile_name', 'label' => 'Perfil'],
            ['key' => 'companies_names', 'label' => 'Empresas'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $users->map(function ($user) {
            // Formatar telefone
            $phone = $user->phone ? $this->formatPhone($user->phone) : '';

            // Nomes das empresas
            $companiesNames = $user->companies->pluck('name')->join(', ');

            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $phone,
                'profile_name' => $user->profile->display_name,
                'companies_names' => $companiesNames,
                'created_at' => $user->created_at->format('d/m/Y H:i:s'),
            ];
        });

        return ExportFactory::exportToExcel($data->toArray(), $headers, 'usuarios', 'Relatório de Usuários');
    }

    /**
     * Export users to PDF
     */
    public function exportToPDF(array $filters = []): Response
    {
        $users = $this->getAllUsers($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome'],
            ['key' => 'email', 'label' => 'Email'],
            ['key' => 'phone', 'label' => 'Telefone'],
            ['key' => 'profile_name', 'label' => 'Perfil'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $users->map(function ($user) {
            // Formatar telefone
            $phone = $user->phone ? $this->formatPhone($user->phone) : '';


            return [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $phone,
                'profile_name' => $user->profile->display_name,
                'created_at' => $user->created_at->format('d/m/Y H:i:s'),
            ];
        });

        return ExportFactory::exportToPDF($data->toArray(), $headers, 'usuarios', 'Relatório de Usuários');
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
     * Associar usuário autenticado a empresas
     */
    public function associateUserToCompanies(User $user, array $companyIds, ?int $profileId = null): User
    {
        // Validar se pelo menos uma empresa foi fornecida
        if (empty($companyIds)) {
            throw new \InvalidArgumentException('Selecione pelo menos uma empresa');
        }

        // Se profile_id foi fornecido, usar attach com dados da pivot
        if ($profileId) {
            // Verificar se o perfil existe
            $profile = Profile::find($profileId);
            if (!$profile) {
                throw new \InvalidArgumentException('Perfil não encontrado');
            }

            // Preparar dados para inserção em lote
            $companiesToAttach = [];

            foreach ($companyIds as $companyId) {
                // Verificar se já está associado
                if (!$user->companies()->where('companies.id', $companyId)->exists()) {
                    $companiesToAttach[$companyId] = [
                        'profile_id' => $profileId,
                        'is_main_company' => false, // Não é principal quando associado via perfil
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            // Inserir em lote se houver empresas para associar
            if (!empty($companiesToAttach)) {
                $user->companies()->attach($companiesToAttach);
            }
        } else {
            // Comportamento original: sincronizar sem dados da pivot
            $user->companies()->syncWithoutDetaching($companyIds);
        }

        // Recarregar relacionamentos com pivot
        return $user->load(['companies' => function ($query) {
            $query->withPivot('profile_id', 'is_main_company');
        }]);
    }

    /**
     * Desassociar usuário de uma empresa
     */
    public function disassociateUserFromCompany(User $user, int $companyId): User
    {
        // Verificar se o usuário está associado à empresa
        if (!$user->companies()->where('companies.id', $companyId)->exists()) {
            throw new \InvalidArgumentException('Usuário não está associado a esta empresa');
        }

        // Verificar se não é a única empresa do usuário
        if ($user->companies()->count() <= 1) {
            throw new \InvalidArgumentException('Você precisa estar vinculado a pelo menos uma empresa');
        }

        $user->companies()->detach($companyId);

        // Recarregar relacionamentos
        return $user->load(['companies' => function ($query) {
            $query->withPivot('profile_id', 'is_main_company');
        }]);
    }

    /**
     * Definir empresa principal do usuário
     */
    public function setMainCompany(User $user, int $companyId): User
    {
        // Verificar se o usuário está associado à empresa
        if (!$user->companies()->where('companies.id', $companyId)->exists()) {
            throw new \InvalidArgumentException('Usuário não está associado a esta empresa');
        }

        // Remover empresa principal de todas as empresas
        $user->companies()->updateExistingPivot($user->companies->pluck('id')->toArray(), [
            'is_main_company' => false
        ]);

        // Definir a nova empresa principal
        $user->companies()->updateExistingPivot($companyId, [
            'is_main_company' => true
        ]);

        // Recarregar relacionamentos
        return $user->load(['companies' => function ($query) {
            $query->withPivot('profile_id', 'is_main_company');
        }]);
    }

    /**
     * Remover empresa principal do usuário
     */
    public function removeMainCompany(User $user, int $companyId): User
    {
        // Verificar se o usuário está associado à empresa
        if (!$user->companies()->where('companies.id', $companyId)->exists()) {
            throw new \InvalidArgumentException('Usuário não está associado a esta empresa');
        }

        // Remover empresa principal
        $user->companies()->updateExistingPivot($companyId, [
            'is_main_company' => false
        ]);

        // Recarregar relacionamentos
        return $user->load(['companies' => function ($query) {
            $query->withPivot('profile_id', 'is_main_company');
        }]);
    }

    /**
     * Formatar dados das empresas do usuário para resposta
     */
    public function formatUserCompanies(User $user): array
    {
        return $user->companies->map(function ($company) {
            return [
                'id' => $company->id,
                'name' => $company->name,
                'pivot' => [
                    'profile_id' => $company->pivot->profile_id ?? null,
                    'is_main_company' => $company->pivot->is_main_company ?? false
                ]
            ];
        })->toArray();
    }
}
