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
        $ownerProfile = Profile::where('name', 'owner')->first();
        $supervisorProfile = Profile::where('name', 'supervisor')->first();
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
                'cpf' => '11111111111',
                'profile_id' => $adminProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Marlo Marques da Silva Filho',
                'email' => 'marlosilva.f2@gmail.com',
                'password' => Hash::make('12345678'),
                'phone' => '71991717209',
                'cpf' => '03296244581',
                'profile_id' => $adminProfile->id,
                'companies' => [$companies->first()->id, 2],
            ],
            [
                'name' => 'Carlos Proprietário',
                'email' => 'proprietario@empresa.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999997777',
                'cpf' => '33333333333',
                'has_whatsapp' => true,
                'profile_id' => $ownerProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Marcos Supervisor',
                'email' => 'supervisor@empresa.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999996666',
                'cpf' => '44444444444',
                'has_whatsapp' => true,
                'profile_id' => $supervisorProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Dr. João Silva',
                'email' => 'joao@empresa.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999995555',
                'cpf' => '55555555555',
                'has_whatsapp' => true,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Cliente Ana',
                'email' => 'ana@cliente.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999994444',
                'cpf' => '66666666666',
                'has_whatsapp' => true,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],

            // Clientes adicionais
            [
                'name' => 'Maria Silva Santos',
                'email' => 'maria.silva@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999991111',
                'cpf' => '12345678901',
                'has_whatsapp' => true,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'João Pedro Oliveira',
                'email' => 'joao.pedro@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999992222',
                'cpf' => '12345678902',
                'has_whatsapp' => true,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Ana Carolina Costa',
                'email' => 'ana.carolina@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999993333',
                'cpf' => '12345678903',
                'has_whatsapp' => false,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Carlos Eduardo Lima',
                'email' => 'carlos.eduardo@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999994445',
                'cpf' => '12345678904',
                'has_whatsapp' => true,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Fernanda Rodrigues',
                'email' => 'fernanda.rodrigues@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999995555',
                'cpf' => '12345678905',
                'has_whatsapp' => true,
                'profile_id' => $clientProfile->id,
                'companies' => [$companies->first()->id],
            ],

            // Profissionais adicionais
            [
                'name' => 'Dr. Roberto Almeida',
                'email' => 'roberto.almeida@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999996666',
                'cpf' => '12345678906',
                'has_whatsapp' => true,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Dra. Juliana Mendes',
                'email' => 'juliana.mendes@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999997777',
                'cpf' => '12345678907',
                'has_whatsapp' => true,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Dr. Marcelo Ferreira',
                'email' => 'marcelo.ferreira@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999998888',
                'cpf' => '12345678908',
                'has_whatsapp' => false,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Dra. Patricia Souza',
                'email' => 'patricia.souza@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999999999',
                'cpf' => '12345678909',
                'has_whatsapp' => true,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
            [
                'name' => 'Dr. Ricardo Barbosa',
                'email' => 'ricardo.barbosa@email.com',
                'password' => Hash::make('12345678'),
                'phone' => '11999990000',
                'cpf' => '12345678910',
                'has_whatsapp' => true,
                'profile_id' => $professionalProfile->id,
                'companies' => [$companies->first()->id],
            ],
        ];

        foreach ($users as $userData) {
            $companies = $userData['companies'];
            unset($userData['companies']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Sincroniza as empresas (evita duplicação)
            $user->companies()->sync($companies);

        }

    }
}
