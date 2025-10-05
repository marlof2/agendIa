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
        $secretaryProfile = Profile::where('name', 'secretary')->first();
        $professionalProfile = Profile::where('name', 'professional')->first();
        $clientProfile = Profile::where('name', 'client')->first();

        // Buscar abilities por categoria
        $userAbilities = Ability::where('category', 'users')->get();
        $companyAbilities = Ability::where('category', 'companies')->get();
        $profileAbilities = Ability::where('category', 'profiles')->get();
        $abilityAbilities = Ability::where('category', 'abilities')->get();
        $dashboardAbilities = Ability::where('category', 'dashboard')->get();
        $companyUserAbilities = Ability::where('category', 'company_users')->get();

        // ADMIN: Todas as permissões
        if ($adminProfile) {
            $allAbilities = Ability::all();
            $adminProfile->abilities()->sync($allAbilities->pluck('id')->toArray());
            $this->command->info('Permissões do Admin configuradas: TODAS');
        }

        // SECRETARY: Permissões limitadas
        if ($secretaryProfile) {
            $secretaryPermissions = collect()
                ->merge($dashboardAbilities)
                ->merge($userAbilities->whereIn('action', ['index', 'show']))
                ->merge($companyUserAbilities->whereIn('action', ['index', 'add', 'remove']))
                ->pluck('id')
                ->toArray();

            $secretaryProfile->abilities()->sync($secretaryPermissions);
            $this->command->info('Permissões da Secretária configuradas: ' . count($secretaryPermissions) . ' permissões');
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
            $this->command->info('Permissões do Profissional configuradas: ' . count($professionalPermissions) . ' permissões');
        }

        // CLIENT: Permissões mínimas
        if ($clientProfile) {
            $clientPermissions = collect()
                ->merge($dashboardAbilities)
                ->merge($userAbilities->whereIn('action', ['show']))
                ->pluck('id')
                ->toArray();

            $clientProfile->abilities()->sync($clientPermissions);
            $this->command->info('Permissões do Cliente configuradas: ' . count($clientPermissions) . ' permissões');
        }

        $this->command->info('Permissões dos perfis configuradas com sucesso!');
    }
}
