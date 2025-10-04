<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar perfis
        $adminProfile = Profile::where('name', 'admin')->first();
        $secretaryProfile = Profile::where('name', 'secretary')->first();
        $clientProfile = Profile::where('name', 'client')->first();

        if (!$adminProfile || !$secretaryProfile || !$clientProfile) {
            $this->command->error('Perfis não encontrados! Execute o ProfileSeeder primeiro.');
            return;
        }

        // Buscar empresas
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->command->error('Nenhuma empresa encontrada! Execute o CompanySeeder primeiro.');
            return;
        }

        $users = [
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@sistema.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999998888',
                'profile_id' => $adminProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Marlo Marques da Silva Filho',
                'email' => 'marlosilva.f2@gmail.com',
                'password' => Hash::make('123456'),
                'phone' => '71991717209',
                'profile_id' => $adminProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Maria Silva Santos',
                'email' => 'maria@techsolutions.com',
                'password' => Hash::make('12345678'),
                'phone' => '11988887777',
                'profile_id' => $secretaryProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'João Oliveira Costa',
                'email' => 'joao.oliveira@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11977776666',
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Ana Paula Ferreira',
                'email' => 'ana@techsolutions.com',
                'password' => Hash::make('12345678'),
                'phone' => '11966665555',
                'profile_id' => $secretaryProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Pedro Henrique Lima',
                'email' => 'pedro.lima@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11955554444',
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Carla Mendes',
                'email' => 'carla@techsolutions.com',
                'password' => Hash::make('12345678'),
                'phone' => '11944443333',
                'profile_id' => $adminProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Roberto Carlos Silva',
                'email' => 'roberto@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11933332222',
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Fernanda Alves',
                'email' => 'fernanda@techsolutions.com',
                'password' => Hash::make('12345678'),
                'phone' => '11922221111',
                'profile_id' => $secretaryProfile->id,
                'companies' => [$companies->first()->id],
            ],
        ];

        foreach ($users as $userData) {
            $companyIds = $userData['companies'];
            unset($userData['companies']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Attach to companies
            $user->companies()->sync($companyIds);
        }

        $this->command->info('Usuários criados com sucesso!');
    }
}
