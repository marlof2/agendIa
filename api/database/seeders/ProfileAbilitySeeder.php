<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\Ability;

class ProfileAbilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar perfis
        $adminProfile = Profile::where('name', 'admin')->first();
        $ownerProfile = Profile::where('name', 'owner')->first();
        $supervisorProfile = Profile::where('name', 'supervisor')->first();
        $professionalProfile = Profile::where('name', 'professional')->first();
        $clientProfile = Profile::where('name', 'client')->first();

        // Buscar abilities por categoria
        $userAbilities = Ability::where('category', 'users')->get();
        $dashboardAbilities = Ability::where('category', 'dashboard')->get();
        $companyUserAbilities = Ability::where('category', 'company_users')->get();

        // ADMIN: Todas as permissões
        if ($adminProfile) {
            $allAbilities = Ability::all();
            $adminProfile->abilities()->sync($allAbilities->pluck('id')->toArray());
        }

        // OWNER: Quase todas as permissões (exceto configurações críticas do sistema)
        if ($ownerProfile) {
            $allAbilities = Ability::all();
            $ownerProfile->abilities()->sync($allAbilities->pluck('id')->toArray());
        }

        // SUPERVISOR: Permissões de gestão
        if ($supervisorProfile) {
            $supervisorPermissions = collect()
                ->merge($dashboardAbilities)
                ->merge($userAbilities->whereIn('action', ['index', 'show', 'store', 'update']))
                ->merge($companyUserAbilities->whereIn('action', ['index', 'add', 'remove']))
                ->pluck('id')
                ->toArray();

            $supervisorProfile->abilities()->sync($supervisorPermissions);
        }

        // PROFESSIONAL: Permissões básicas
        if ($professionalProfile) {
            $professionalPermissions = collect()
                ->merge($dashboardAbilities)
                ->merge($userAbilities->whereIn('action', ['index', 'show']))
                ->merge($companyUserAbilities->whereIn('action', ['index']))
                ->pluck('id')
                ->toArray();

            $professionalProfile->abilities()->sync($professionalPermissions);
        }

        // CLIENT: Permissões mínimas
        if ($clientProfile) {
            $clientPermissions = collect()
                ->merge($dashboardAbilities)
                ->merge($userAbilities->whereIn('action', ['show']))
                ->pluck('id')
                ->toArray();

            $clientProfile->abilities()->sync($clientPermissions);
        }

    }
}
