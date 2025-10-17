<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ability;

class AbilitySeeder extends Seeder
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
            ['name' => 'users.show', 'category' => 'users', 'action' => 'show', 'display_name' => 'Visualizar Usuários', 'description' => 'Permite visualizar usuários'],
            ['name' => 'users.delete', 'category' => 'users', 'action' => 'delete', 'display_name' => 'Deletar Usuários', 'description' => 'Permite deletar usuários'],
            ['name' => 'users.associate_companies', 'category' => 'users', 'action' => 'associate_companies', 'display_name' => 'Associar Empresas aos Usuários', 'description' => 'Permite associar empresas aos usuários'],
            ['name' => 'users.detach_company', 'category' => 'users', 'action' => 'detach_company', 'display_name' => 'Desassociar Empresa dos Usuários', 'description' => 'Permite desassociar empresa dos usuários'],
            ['name' => 'users.my_companies', 'category' => 'users', 'action' => 'my_companies', 'display_name' => 'Minhas Empresas', 'description' => 'Permite gerenciar minhas empresas'],


            // Empresas
            ['name' => 'companies.index', 'category' => 'companies', 'action' => 'index', 'display_name' => 'Listar Empresas', 'description' => 'Permite visualizar empresas'],
            ['name' => 'companies.create', 'category' => 'companies', 'action' => 'create', 'display_name' => 'Criar Empresas', 'description' => 'Permite criar novas empresas'],
            ['name' => 'companies.edit', 'category' => 'companies', 'action' => 'edit', 'display_name' => 'Editar Empresas', 'description' => 'Permite editar empresas existentes'],
            ['name' => 'companies.show', 'category' => 'companies', 'action' => 'show', 'display_name' => 'Visualizar Empresas', 'description' => 'Permite visualizar empresas'],
            ['name' => 'companies.delete', 'category' => 'companies', 'action' => 'delete', 'display_name' => 'Deletar Empresas', 'description' => 'Permite deletar empresas'],
            ['name' => 'companies.deactivate', 'category' => 'companies', 'action' => 'deactivate', 'display_name' => 'Inativar Empresas', 'description' => 'Permite inativar empresas'],
            ['name' => 'companies.activate', 'category' => 'companies', 'action' => 'activate', 'display_name' => 'Ativar Empresas', 'description' => 'Permite ativar empresas'],
            ['name' => 'companies.manage_users', 'category' => 'companies', 'action' => 'manage_users', 'display_name' => 'Gerenciar Usuários das Empresas', 'description' => 'Permite gerenciar usuários das empresas'],

            ['name' => 'companies.users.index', 'category' => 'companies', 'action' => 'users.index', 'display_name' => 'Listar Usuários das Empresas', 'description' => 'Permite visualizar usuários das empresas'],
            ['name' => 'companies.users.attach', 'category' => 'companies', 'action' => 'users.attach', 'display_name' => 'Associar Usuário à Empresa', 'description' => 'Permite associar usuário à empresa'],
            ['name' => 'companies.users.show', 'category' => 'companies', 'action' => 'users.show', 'display_name' => 'Visualizar Usuário da Empresa', 'description' => 'Permite visualizar usuário da empresa'],
            ['name' => 'companies.users.update_profile', 'category' => 'companies', 'action' => 'users.update_profile', 'display_name' => 'Atualizar Usuário da Empresa', 'description' => 'Permite atualizar o perfil do usuário da empresa'],
            ['name' => 'companies.users.detach', 'category' => 'companies', 'action' => 'users.detach', 'display_name' => 'Desassociar Usuário da Empresa', 'description' => 'Permite desassociar usuário da empresa'],


            // Perfis
            ['name' => 'profiles.index', 'category' => 'profiles', 'action' => 'index', 'display_name' => 'Listar Perfis', 'description' => 'Permite visualizar perfis'],
            ['name' => 'profiles.create', 'category' => 'profiles', 'action' => 'create', 'display_name' => 'Criar Perfis', 'description' => 'Permite criar perfis'],
            ['name' => 'profiles.edit', 'category' => 'profiles', 'action' => 'edit', 'display_name' => 'Editar Perfis', 'description' => 'Permite editar perfis'],
            ['name' => 'profiles.delete', 'category' => 'profiles', 'action' => 'delete', 'display_name' => 'Deletar Perfis', 'description' => 'Permite deletar perfis'],
            ['name' => 'profiles.show', 'category' => 'profiles', 'action' => 'show', 'display_name' => 'Visualizar Perfis', 'description' => 'Permite visualizar perfis'],
            ['name' => 'profiles.list_abilities', 'category' => 'profiles', 'action' => 'list_abilities', 'display_name' => 'Listar Abilidades dos Perfis', 'description' => 'Permite listar habilidades dos perfis'],
            ['name' => 'profiles.update_abilities', 'category' => 'profiles', 'action' => 'update_abilities', 'display_name' => 'Atualizar Abilidades dos Perfis', 'description' => 'Permite atualizar habilidades dos perfis'],

             // Clientes
            ['name' => 'clients.index', 'category' => 'clients', 'action' => 'index', 'display_name' => 'Listar Clientes', 'description' => 'Permite visualizar clientes'],
            ['name' => 'clients.show', 'category' => 'clients', 'action' => 'show', 'display_name' => 'Visualizar Cliente', 'description' => 'Permite visualizar detalhes do cliente'],
            ['name' => 'clients.create', 'category' => 'clients', 'action' => 'create', 'display_name' => 'Criar Clientes', 'description' => 'Permite criar novos clientes'],
            ['name' => 'clients.edit', 'category' => 'clients', 'action' => 'edit', 'display_name' => 'Editar Clientes', 'description' => 'Permite editar clientes existentes'],
            ['name' => 'clients.delete', 'category' => 'clients', 'action' => 'delete', 'display_name' => 'Deletar Clientes', 'description' => 'Permite deletar clientes'],

            //sistema
            ['name' => 'abilities.index', 'category' => 'others', 'action' => 'index', 'display_name' => 'Listar Abilidades', 'description' => 'Permite visualizar habilidades'],
            ['name' => 'dashboard', 'category' => 'others', 'action' => 'dashboard', 'display_name' => 'Visualizar Dashboard', 'description' => 'Permite visualizar o dashboard'],


            // // Agendamentos
            // ['name' => 'appointments.index', 'category' => 'appointments', 'action' => 'index', 'display_name' => 'Listar Agendamentos', 'description' => 'Permite visualizar agendamentos'],
            // ['name' => 'appointments.create', 'category' => 'appointments', 'action' => 'create', 'display_name' => 'Criar Agendamentos', 'description' => 'Permite criar novos agendamentos'],
            // ['name' => 'appointments.edit', 'category' => 'appointments', 'action' => 'edit', 'display_name' => 'Editar Agendamentos', 'description' => 'Permite editar agendamentos existentes'],
            // ['name' => 'appointments.delete', 'category' => 'appointments', 'action' => 'delete', 'display_name' => 'Deletar Agendamentos', 'description' => 'Permite deletar agendamentos'],
            // ['name' => 'appointments.cancel', 'category' => 'appointments', 'action' => 'cancel', 'display_name' => 'Cancelar Agendamentos', 'description' => 'Permite cancelar agendamentos'],
            // ['name' => 'appointments.reschedule', 'category' => 'appointments', 'action' => 'reschedule', 'display_name' => 'Remarcar Agendamentos', 'description' => 'Permite remarcar agendamentos'],

            // Horários
            // ['name' => 'schedules.index', 'category' => 'schedules', 'action' => 'index', 'display_name' => 'Listar Horários', 'description' => 'Permite visualizar horários de atendimento'],
            // ['name' => 'schedules.create', 'category' => 'schedules', 'action' => 'create', 'display_name' => 'Criar Horários', 'description' => 'Permite criar horários de atendimento'],
            // ['name' => 'schedules.edit', 'category' => 'schedules', 'action' => 'edit', 'display_name' => 'Editar Horários', 'description' => 'Permite editar horários de atendimento'],
            // ['name' => 'schedules.delete', 'category' => 'schedules', 'action' => 'delete', 'display_name' => 'Deletar Horários', 'description' => 'Permite deletar horários de atendimento'],

            // Serviços
            // ['name' => 'services.index', 'category' => 'services', 'action' => 'index', 'display_name' => 'Listar Serviços', 'description' => 'Permite visualizar serviços'],
            // ['name' => 'services.create', 'category' => 'services', 'action' => 'create', 'display_name' => 'Criar Serviços', 'description' => 'Permite criar novos serviços'],
            // ['name' => 'services.edit', 'category' => 'services', 'action' => 'edit', 'display_name' => 'Editar Serviços', 'description' => 'Permite editar serviços existentes'],
            // ['name' => 'services.delete', 'category' => 'services', 'action' => 'delete', 'display_name' => 'Deletar Serviços', 'description' => 'Permite deletar serviços'],

            // Relatórios
            // ['name' => 'reports.index', 'category' => 'reports', 'action' => 'index', 'display_name' => 'Listar Relatórios', 'description' => 'Permite visualizar relatórios'],
            // ['name' => 'reports.create', 'category' => 'reports', 'action' => 'create', 'display_name' => 'Criar Relatórios', 'description' => 'Permite criar relatórios'],
            // ['name' => 'reports.export', 'category' => 'reports', 'action' => 'export', 'display_name' => 'Exportar Relatórios', 'description' => 'Permite exportar relatórios'],

            // Configurações
            // ['name' => 'settings.index', 'category' => 'settings', 'action' => 'index', 'display_name' => 'Listar Configurações', 'description' => 'Permite visualizar configurações'],
            // ['name' => 'settings.edit', 'category' => 'settings', 'action' => 'edit', 'display_name' => 'Editar Configurações', 'description' => 'Permite editar configurações'],

            // Notificações
            // ['name' => 'notifications.index', 'category' => 'notifications', 'action' => 'index', 'display_name' => 'Listar Notificações', 'description' => 'Permite visualizar notificações'],
            // ['name' => 'notifications.create', 'category' => 'notifications', 'action' => 'create', 'display_name' => 'Criar Notificações', 'description' => 'Permite criar notificações'],
            // ['name' => 'notifications.edit', 'category' => 'notifications', 'action' => 'edit', 'display_name' => 'Editar Notificações', 'description' => 'Permite editar notificações'],

            // // Integrações
            // ['name' => 'integrations.index', 'category' => 'integrations', 'action' => 'index', 'display_name' => 'Listar Integrações', 'description' => 'Permite visualizar integrações'],
            // ['name' => 'integrations.create', 'category' => 'integrations', 'action' => 'create', 'display_name' => 'Criar Integrações', 'description' => 'Permite criar integrações'],
            // ['name' => 'integrations.edit', 'category' => 'integrations', 'action' => 'edit', 'display_name' => 'Editar Integrações', 'description' => 'Permite editar integrações'],


        ];

        // Criar abilities usando firstOrCreate para evitar duplicatas
        foreach ($abilities as $abilityData) {
            Ability::firstOrCreate(
                ['name' => $abilityData['name']],
                $abilityData
            );
        }

    }
}
