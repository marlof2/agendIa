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
        $professionalProfile = Profile::where('name', 'professional')->first();
        $clientProfile = Profile::where('name', 'client')->first();

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
                'name' => 'Secretária Maria',
                'email' => 'secretaria@empresa.com',
                'password' => Hash::make('123456'),
                'phone' => '11999997777',
                'has_whatsapp' => true,
                'profile_id' => $secretaryProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Dr. João Silva',
                'email' => 'joao@empresa.com',
                'password' => Hash::make('123456'),
                'phone' => '11999996666',
                'has_whatsapp' => true,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Cliente Ana',
                'email' => 'ana@cliente.com',
                'password' => Hash::make('123456'),
                'phone' => '11999995555',
                'has_whatsapp' => true,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
        ];

        foreach ($users as $userData) {
            $companies = $userData['companies'];
            unset($userData['companies']);

            $user = User::create($userData);
            $user->companies()->attach($companies);

            $this->command->info("Usuário criado: {$user->name} ({$user->email})");
        }

        $this->command->info('Usuários criados com sucesso!');
    }
}
