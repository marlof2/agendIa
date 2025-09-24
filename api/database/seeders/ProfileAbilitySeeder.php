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
        // Criar abilities com novo padrão
        $abilities = [
            // Usuários
            ['name' => 'users.index', 'category' => 'users', 'action' => 'index', 'display_name' => 'Listar Usuários', 'description' => 'Permite visualizar a lista de usuários'],
            ['name' => 'users.create', 'category' => 'users', 'action' => 'create', 'display_name' => 'Criar Usuários', 'description' => 'Permite criar novos usuários'],
            ['name' => 'users.edit', 'category' => 'users', 'action' => 'edit', 'display_name' => 'Editar Usuários', 'description' => 'Permite editar usuários existentes'],
            ['name' => 'users.delete', 'category' => 'users', 'action' => 'delete', 'display_name' => 'Deletar Usuários', 'description' => 'Permite deletar usuários'],

            // Empresas
            ['name' => 'companies.index', 'category' => 'companies', 'action' => 'index', 'display_name' => 'Listar Empresas', 'description' => 'Permite visualizar empresas'],
            ['name' => 'companies.create', 'category' => 'companies', 'action' => 'create', 'display_name' => 'Criar Empresas', 'description' => 'Permite criar novas empresas'],
            ['name' => 'companies.edit', 'category' => 'companies', 'action' => 'edit', 'display_name' => 'Editar Empresas', 'description' => 'Permite editar empresas existentes'],
            ['name' => 'companies.delete', 'category' => 'companies', 'action' => 'delete', 'display_name' => 'Deletar Empresas', 'description' => 'Permite deletar empresas'],

            // Agendamentos
            ['name' => 'appointments.index', 'category' => 'appointments', 'action' => 'index', 'display_name' => 'Listar Agendamentos', 'description' => 'Permite visualizar agendamentos'],
            ['name' => 'appointments.create', 'category' => 'appointments', 'action' => 'create', 'display_name' => 'Criar Agendamentos', 'description' => 'Permite criar novos agendamentos'],
            ['name' => 'appointments.edit', 'category' => 'appointments', 'action' => 'edit', 'display_name' => 'Editar Agendamentos', 'description' => 'Permite editar agendamentos existentes'],
            ['name' => 'appointments.delete', 'category' => 'appointments', 'action' => 'delete', 'display_name' => 'Deletar Agendamentos', 'description' => 'Permite deletar agendamentos'],
            ['name' => 'appointments.cancel', 'category' => 'appointments', 'action' => 'cancel', 'display_name' => 'Cancelar Agendamentos', 'description' => 'Permite cancelar agendamentos'],
            ['name' => 'appointments.reschedule', 'category' => 'appointments', 'action' => 'reschedule', 'display_name' => 'Remarcar Agendamentos', 'description' => 'Permite remarcar agendamentos'],

            // Horários
            ['name' => 'schedules.index', 'category' => 'schedules', 'action' => 'index', 'display_name' => 'Listar Horários', 'description' => 'Permite visualizar horários de atendimento'],
            ['name' => 'schedules.create', 'category' => 'schedules', 'action' => 'create', 'display_name' => 'Criar Horários', 'description' => 'Permite criar horários de atendimento'],
            ['name' => 'schedules.edit', 'category' => 'schedules', 'action' => 'edit', 'display_name' => 'Editar Horários', 'description' => 'Permite editar horários de atendimento'],
            ['name' => 'schedules.delete', 'category' => 'schedules', 'action' => 'delete', 'display_name' => 'Deletar Horários', 'description' => 'Permite deletar horários de atendimento'],

            // Serviços
            ['name' => 'services.index', 'category' => 'services', 'action' => 'index', 'display_name' => 'Listar Serviços', 'description' => 'Permite visualizar serviços'],
            ['name' => 'services.create', 'category' => 'services', 'action' => 'create', 'display_name' => 'Criar Serviços', 'description' => 'Permite criar novos serviços'],
            ['name' => 'services.edit', 'category' => 'services', 'action' => 'edit', 'display_name' => 'Editar Serviços', 'description' => 'Permite editar serviços existentes'],
            ['name' => 'services.delete', 'category' => 'services', 'action' => 'delete', 'display_name' => 'Deletar Serviços', 'description' => 'Permite deletar serviços'],

            // Relatórios
            ['name' => 'reports.index', 'category' => 'reports', 'action' => 'index', 'display_name' => 'Listar Relatórios', 'description' => 'Permite visualizar relatórios'],
            ['name' => 'reports.create', 'category' => 'reports', 'action' => 'create', 'display_name' => 'Criar Relatórios', 'description' => 'Permite criar relatórios'],
            ['name' => 'reports.export', 'category' => 'reports', 'action' => 'export', 'display_name' => 'Exportar Relatórios', 'description' => 'Permite exportar relatórios'],

            // Configurações
            ['name' => 'settings.index', 'category' => 'settings', 'action' => 'index', 'display_name' => 'Listar Configurações', 'description' => 'Permite visualizar configurações'],
            ['name' => 'settings.edit', 'category' => 'settings', 'action' => 'edit', 'display_name' => 'Editar Configurações', 'description' => 'Permite editar configurações'],

            // Notificações
            ['name' => 'notifications.index', 'category' => 'notifications', 'action' => 'index', 'display_name' => 'Listar Notificações', 'description' => 'Permite visualizar notificações'],
            ['name' => 'notifications.create', 'category' => 'notifications', 'action' => 'create', 'display_name' => 'Criar Notificações', 'description' => 'Permite criar notificações'],
            ['name' => 'notifications.edit', 'category' => 'notifications', 'action' => 'edit', 'display_name' => 'Editar Notificações', 'description' => 'Permite editar notificações'],

            // Integrações
            ['name' => 'integrations.index', 'category' => 'integrations', 'action' => 'index', 'display_name' => 'Listar Integrações', 'description' => 'Permite visualizar integrações'],
            ['name' => 'integrations.create', 'category' => 'integrations', 'action' => 'create', 'display_name' => 'Criar Integrações', 'description' => 'Permite criar integrações'],
            ['name' => 'integrations.edit', 'category' => 'integrations', 'action' => 'edit', 'display_name' => 'Editar Integrações', 'description' => 'Permite editar integrações'],

            // Perfis
            ['name' => 'profiles.index', 'category' => 'profiles', 'action' => 'index', 'display_name' => 'Listar Perfis', 'description' => 'Permite visualizar perfis'],
            ['name' => 'profiles.create', 'category' => 'profiles', 'action' => 'create', 'display_name' => 'Criar Perfis', 'description' => 'Permite criar perfis'],
            ['name' => 'profiles.edit', 'category' => 'profiles', 'action' => 'edit', 'display_name' => 'Editar Perfis', 'description' => 'Permite editar perfis'],
            ['name' => 'profiles.delete', 'category' => 'profiles', 'action' => 'delete', 'display_name' => 'Deletar Perfis', 'description' => 'Permite deletar perfis'],

            // Clientes
            ['name' => 'clients.index', 'category' => 'clients', 'action' => 'index', 'display_name' => 'Listar Clientes', 'description' => 'Permite visualizar clientes'],
            ['name' => 'clients.create', 'category' => 'clients', 'action' => 'create', 'display_name' => 'Criar Clientes', 'description' => 'Permite criar novos clientes'],
            ['name' => 'clients.edit', 'category' => 'clients', 'action' => 'edit', 'display_name' => 'Editar Clientes', 'description' => 'Permite editar clientes existentes'],
            ['name' => 'clients.delete', 'category' => 'clients', 'action' => 'delete', 'display_name' => 'Deletar Clientes', 'description' => 'Permite deletar clientes'],
            ['name' => 'clients.view', 'category' => 'clients', 'action' => 'view', 'display_name' => 'Visualizar Cliente', 'description' => 'Permite visualizar detalhes do cliente'],
        ];

        // Criar abilities usando firstOrCreate para evitar duplicatas
        foreach ($abilities as $abilityData) {
            Ability::firstOrCreate(
                ['name' => $abilityData['name']],
                $abilityData
            );
        }

        // Buscar perfil admin
        $adminProfile = Profile::where('name', 'admin')->first();

        if ($adminProfile) {
            // Buscar todas as abilities
            $allAbilities = Ability::all();

            // Associar todas as abilities ao perfil admin
            $adminProfile->abilities()->sync($allAbilities->pluck('id')->toArray());
        }
    }
}
