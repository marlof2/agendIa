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

class UserService
{
    /**
     * Get all users with pagination and search
     */
    public function getAllUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['profile', 'companies']);

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by profile
        if (!empty($filters['profile_id'])) {
            $query->where('profile_id', $filters['profile_id']);
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
        return User::with(['profile', 'companies'])->findOrFail($id);
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

        return $user->load(['profile', 'companies']);
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

        // Handle company attachments
        if (isset($cleanedData['company_ids'])) {
            $user->companies()->sync($cleanedData['company_ids']);
            unset($cleanedData['company_ids']);
        }

        $user->update($cleanedData);

        return $user->load(['profile', 'companies']);
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
     * Buscar profissionais disponíveis para associação a uma empresa
     */
    public function getAvailableProfessionals(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.phone',
            'users.has_whatsapp',
            'users.profile_id',
            'users.created_at'
        ])
        ->join('profiles', 'users.profile_id', '=', 'profiles.id')
        ->where('profiles.name', 'professional');

        // Filtrar usuários que NÃO estão associados à empresa
        if (!empty($filters['company_id'])) {
            $query->whereDoesntHave('companies', function ($q) use ($filters) {
                $q->where('companies.id', $filters['company_id']);
            });
        }

        // Busca por nome, email ou telefone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.phone', 'like', "%{$search}%");
            });
        }

        // Paginação
        return $query->orderBy('users.name', 'asc')->paginate($filters['per_page']);
    }

}
