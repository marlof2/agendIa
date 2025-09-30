<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\Ability;
use Faker\Factory as Faker;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Criar perfis básicos primeiro
        $basicProfiles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrador',
                'description' => 'Perfil com acesso total ao sistema',
            ],
            [
                'name' => 'secretary',
                'display_name' => 'Secretária',
                'description' => 'Perfil para secretárias com acesso a agendamentos e clientes',
            ],
            [
                'name' => 'client',
                'display_name' => 'Cliente',
                'description' => 'Perfil para clientes com acesso limitado',
            ],
        ];

        foreach ($basicProfiles as $profileData) {
            Profile::firstOrCreate(
                ['name' => $profileData['name']],
                $profileData
            );
        }

        // Gerar 60 perfis adicionais para teste de paginação
        $profileTypes = [
            'Gerente', 'Supervisor', 'Coordenador', 'Analista', 'Assistente',
            'Consultor', 'Especialista', 'Técnico', 'Operador', 'Atendente',
            'Vendedor', 'Representante', 'Executivo', 'Diretor', 'Coordenador',
            'Líder', 'Facilitador', 'Instrutor', 'Treinador', 'Mentor'
        ];

        $departments = [
            'Vendas', 'Marketing', 'RH', 'Financeiro', 'TI', 'Operações',
            'Atendimento', 'Suporte', 'Qualidade', 'Produção', 'Logística',
            'Comercial', 'Administrativo', 'Jurídico', 'Contábil'
        ];

        $descriptions = [
            'Perfil com acesso completo ao módulo',
            'Perfil para visualização e edição limitada',
            'Perfil com permissões de consulta apenas',
            'Perfil para gerenciamento de dados específicos',
            'Perfil com acesso a relatórios e análises',
            'Perfil para operações do dia a dia',
            'Perfil com permissões administrativas',
            'Perfil para suporte técnico',
            'Perfil com acesso a configurações',
            'Perfil para auditoria e controle'
        ];

        // Buscar algumas abilities para associar aos perfis
        $abilities = Ability::inRandomOrder()->limit(10)->get();

        for ($i = 1; $i <= 60; $i++) {
            $profileType = $faker->randomElement($profileTypes);
            $department = $faker->randomElement($departments);
            $description = $faker->randomElement($descriptions);

            $profile = Profile::create([
                'name' => strtolower($profileType . '_' . $department . '_' . $i),
                'display_name' => $profileType . ' de ' . $department,
                'description' => $description . ' do departamento de ' . $department,
            ]);

            // Associar algumas abilities aleatórias (0 a 5 abilities por perfil)
            $randomAbilities = $abilities->random($faker->numberBetween(0, 5));
            $profile->abilities()->attach($randomAbilities->pluck('id')->toArray());
        }

        $this->command->info('60 perfis de teste criados com sucesso!');
    }
}
