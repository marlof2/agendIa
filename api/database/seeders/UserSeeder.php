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
        $this->command->info('🌱 Criando usuários...');

        // Verificar se existem empresas
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->command->error('❌ Nenhuma empresa encontrada! Execute o CompanySeeder primeiro.');
            return;
        }

        $companyId = $companies->first()->id;
        $companyId2 = $companies->skip(1)->first()?->id ?? $companyId;

        // Criar usuários por categoria
        $this->createAdminUsers($companyId, $companyId2);
        $this->createOwnerUsers($companyId);
        $this->createSupervisorUsers($companyId);
        $this->createProfessionalUsers($companyId);
        $this->createClientUsers($companyId);

        $this->command->info('✅ Usuários criados com sucesso!');
    }

    private function createAdminUsers(int $companyId, int $companyId2): void
    {
        $profile = Profile::where('name', 'admin')->first();

        $users = [
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@sistema.com',
                'phone' => '11999998888',
                'cpf' => '11111111111',
                'companies' => [$companyId],
            ],
            [
                'name' => 'Marlo Marques da Silva Filho',
                'email' => 'marlosilva.f2@gmail.com',
                'phone' => '71991717209',
                'cpf' => '03296244581',
                'companies' => [$companyId, $companyId2],
            ],
        ];

        $this->createUsersWithProfile($users, $profile);
    }

    private function createOwnerUsers(int $companyId): void
    {
        $profile = Profile::where('name', 'owner')->first();

        $users = [
            [
                'name' => 'Carlos Proprietário',
                'email' => 'proprietario@empresa.com',
                'phone' => '11999997777',
                'cpf' => '33333333333',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
        ];

        $this->createUsersWithProfile($users, $profile);
    }

    private function createSupervisorUsers(int $companyId): void
    {
        $profile = Profile::where('name', 'supervisor')->first();

        $users = [
            [
                'name' => 'Marcos Supervisor',
                'email' => 'supervisor@empresa.com',
                'phone' => '11999996666',
                'cpf' => '44444444444',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
        ];

        $this->createUsersWithProfile($users, $profile);
    }

    private function createProfessionalUsers(int $companyId): void
    {
        $profile = Profile::where('name', 'professional')->first();

        $users = [
            [
                'name' => 'Dr. João Silva',
                'email' => 'joao@empresa.com',
                'phone' => '11999995555',
                'cpf' => '55555555555',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Dr. Roberto Almeida',
                'email' => 'roberto.almeida@email.com',
                'phone' => '11999996667',
                'cpf' => '12345678906',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Dra. Juliana Mendes',
                'email' => 'juliana.mendes@email.com',
                'phone' => '11999997778',
                'cpf' => '12345678907',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Dr. Marcelo Ferreira',
                'email' => 'marcelo.ferreira@email.com',
                'phone' => '11999998889',
                'cpf' => '12345678908',
                'has_whatsapp' => false,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Dra. Patricia Souza',
                'email' => 'patricia.souza@email.com',
                'phone' => '11999999990',
                'cpf' => '12345678909',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Dr. Ricardo Barbosa',
                'email' => 'ricardo.barbosa@email.com',
                'phone' => '11999990001',
                'cpf' => '12345678910',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
        ];

        $this->createUsersWithProfile($users, $profile);
    }

    private function createClientUsers(int $companyId): void
    {
        $profile = Profile::where('name', 'client')->first();

        $users = [
            [
                'name' => 'Cliente Ana',
                'email' => 'ana@cliente.com',
                'phone' => '11999994444',
                'cpf' => '66666666666',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Maria Silva Santos',
                'email' => 'maria.silva@email.com',
                'phone' => '11999991111',
                'cpf' => '12345678901',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'João Pedro Oliveira',
                'email' => 'joao.pedro@email.com',
                'phone' => '11999992222',
                'cpf' => '12345678902',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Ana Carolina Costa',
                'email' => 'ana.carolina@email.com',
                'phone' => '11999993333',
                'cpf' => '12345678903',
                'has_whatsapp' => false,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Carlos Eduardo Lima',
                'email' => 'carlos.eduardo@email.com',
                'phone' => '11999994445',
                'cpf' => '12345678904',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
            [
                'name' => 'Fernanda Rodrigues',
                'email' => 'fernanda.rodrigues@email.com',
                'phone' => '11999995556',
                'cpf' => '12345678905',
                'has_whatsapp' => true,
                'companies' => [$companyId],
            ],
        ];

        $this->createUsersWithProfile($users, $profile);
    }

    private function createUsersWithProfile(array $users, Profile $profile): void
    {
        foreach ($users as $userData) {
            $companies = $userData['companies'];
            unset($userData['companies']);

            // Adicionar senha padrão
            $userData['password'] = Hash::make('12345678');

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // Vincular usuário às empresas com o perfil
            $companyData = [];
            foreach ($companies as $index => $companyId) {
                $companyData[$companyId] = [
                    'profile_id' => $profile->id,
                    'is_main_company' => $index === 0 // Primeira empresa é sempre a principal
                ];
            }
            $user->companies()->sync($companyData);

            $this->command->line("   ✓ {$user->name} ({$profile->display_name})");
        }
    }
}
