<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Ability;
use App\Factories\ExportFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProfileService
{
    /**
     * Get all profiles with pagination and search
     */
    public function getAllProfiles(array $filters = []): LengthAwarePaginator
    {
        $query = Profile::with('abilities');

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('display_name', 'like', "%{$search}%");
        }

        // Pagination
        $perPage = $filters['per_page'] ?? 12;

        return $query->paginate($perPage);
    }

    /**
     * Get a specific profile by ID
     */
    public function getProfileById(int $id): Profile
    {
        return Profile::with('abilities')->findOrFail($id);
    }

    /**
     * Create a new profile
     */
    public function createProfile(array $data): Profile
    {
        $profile = Profile::create([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
        ]);

        // Attach abilities if provided
        if (!empty($data['abilities'])) {
            $profile->abilities()->attach($data['abilities']);
        }

        return $profile->load('abilities');
    }

    /**
     * Update an existing profile
     */
    public function updateProfile(Profile $profile, array $data): Profile
    {
        $profile->update([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
        ]);

        // Sync abilities if provided
        if (isset($data['abilities'])) {
            $profile->abilities()->sync($data['abilities']);
        }

        return $profile->load('abilities');
    }

    /**
     * Delete a profile
     */
    public function deleteProfile(Profile $profile): bool
    {
        // Check if profile is being used by any users
        if ($this->isProfileInUse($profile)) {
            throw new \Exception('Não é possível deletar um perfil que está sendo usado por usuários');
        }

        // Detach all abilities
        $profile->abilities()->detach();

        return $profile->delete();
    }

    /**
     * Check if profile is being used by any users
     */
    public function isProfileInUse(Profile $profile): bool
    {
        return $profile->users()->count() > 0;
    }

    /**
     * Update profile abilities
     */
    public function updateProfileAbilities(Profile $profile, array $abilities): Profile
    {
        $profile->abilities()->sync($abilities);

        return $profile->load('abilities');
    }

    /**
     * Get all abilities
     */
    public function getAllAbilities(): Collection
    {
        return Ability::all();
    }

    /**
     * Get profiles for export with search filter
     */
    public function getProfilesForExport(array $filters = []): Collection
    {
        $query = Profile::with('abilities');

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('display_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Export profiles to Excel
     */
    public function exportToExcel(array $filters = []): BinaryFileResponse
    {
        $profiles = $this->getProfilesForExport($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome do Perfil'],
            ['key' => 'display_name', 'label' => 'Nome de Exibição'],
            ['key' => 'description', 'label' => 'Descrição'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $profiles->map(function ($profile) {
            return [
                'name' => $profile->name,
                'display_name' => $profile->display_name,
                'description' => $profile->description,
                'created_at' => $profile->created_at->format('d/m/Y H:i:s'),
            ];
        })->toArray();

        return ExportFactory::exportToExcel($data, $headers, 'perfis', 'Relatório de Perfis');
    }

    /**
     * Export profiles to PDF
     */
    public function exportToPDF(array $filters = []): Response
    {
        $profiles = $this->getProfilesForExport($filters);

        $headers = [
            ['key' => 'name', 'label' => 'Nome do Perfil'],
            ['key' => 'display_name', 'label' => 'Nome de Exibição'],
            ['key' => 'description', 'label' => 'Descrição'],
            ['key' => 'created_at', 'label' => 'Data de Criação'],
        ];

        $data = $profiles->map(function ($profile) {
            return [
                'name' => $profile->name,
                'display_name' => $profile->display_name,
                'description' => $profile->description,
                'created_at' => $profile->created_at->format('d/m/Y H:i:s'),
            ];
        })->toArray();

        return ExportFactory::exportToPDF($data, $headers, 'perfis', 'Relatório de Perfis');
    }
}
