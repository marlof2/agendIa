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
        // Criar abilities
        $abilities = [
            // Usuários
            ['name' => 'users.view', 'category' => 'users', 'action' => 'view', 'display_name' => 'Visualizar Usuários', 'description' => 'Permite visualizar a lista de usuários'],
            ['name' => 'users.create', 'category' => 'users', 'action' => 'create', 'display_name' => 'Criar Usuários', 'description' => 'Permite criar novos usuários'],
            ['name' => 'users.edit', 'category' => 'users', 'action' => 'edit', 'display_name' => 'Editar Usuários', 'description' => 'Permite editar usuários existentes'],
            ['name' => 'users.delete', 'category' => 'users', 'action' => 'delete', 'display_name' => 'Deletar Usuários', 'description' => 'Permite deletar usuários'],

            // Empresas
            ['name' => 'companies.view', 'category' => 'companies', 'action' => 'view', 'display_name' => 'Visualizar Empresas', 'description' => 'Permite visualizar empresas'],
            ['name' => 'companies.create', 'category' => 'companies', 'action' => 'create', 'display_name' => 'Criar Empresas', 'description' => 'Permite criar novas empresas'],
            ['name' => 'companies.edit', 'category' => 'companies', 'action' => 'edit', 'display_name' => 'Editar Empresas', 'description' => 'Permite editar empresas existentes'],
            ['name' => 'companies.delete', 'category' => 'companies', 'action' => 'delete', 'display_name' => 'Deletar Empresas', 'description' => 'Permite deletar empresas'],

            // Agendamentos
            ['name' => 'appointments.view', 'category' => 'appointments', 'action' => 'view', 'display_name' => 'Visualizar Agendamentos', 'description' => 'Permite visualizar agendamentos'],
            ['name' => 'appointments.create', 'category' => 'appointments', 'action' => 'create', 'display_name' => 'Criar Agendamentos', 'description' => 'Permite criar novos agendamentos'],
            ['name' => 'appointments.edit', 'category' => 'appointments', 'action' => 'edit', 'display_name' => 'Editar Agendamentos', 'description' => 'Permite editar agendamentos existentes'],
            ['name' => 'appointments.delete', 'category' => 'appointments', 'action' => 'delete', 'display_name' => 'Deletar Agendamentos', 'description' => 'Permite deletar agendamentos'],
            ['name' => 'appointments.cancel', 'category' => 'appointments', 'action' => 'cancel', 'display_name' => 'Cancelar Agendamentos', 'description' => 'Permite cancelar agendamentos'],
            ['name' => 'appointments.reschedule', 'category' => 'appointments', 'action' => 'reschedule', 'display_name' => 'Remarcar Agendamentos', 'description' => 'Permite remarcar agendamentos'],

            // Horários
            ['name' => 'schedules.view', 'category' => 'schedules', 'action' => 'view', 'display_name' => 'Visualizar Horários', 'description' => 'Permite visualizar horários de atendimento'],
            ['name' => 'schedules.create', 'category' => 'schedules', 'action' => 'create', 'display_name' => 'Criar Horários', 'description' => 'Permite criar horários de atendimento'],
            ['name' => 'schedules.edit', 'category' => 'schedules', 'action' => 'edit', 'display_name' => 'Editar Horários', 'description' => 'Permite editar horários de atendimento'],
            ['name' => 'schedules.delete', 'category' => 'schedules', 'action' => 'delete', 'display_name' => 'Deletar Horários', 'description' => 'Permite deletar horários de atendimento'],

            // Serviços
            ['name' => 'services.view', 'category' => 'services', 'action' => 'view', 'display_name' => 'Visualizar Serviços', 'description' => 'Permite visualizar serviços'],
            ['name' => 'services.create', 'category' => 'services', 'action' => 'create', 'display_name' => 'Criar Serviços', 'description' => 'Permite criar novos serviços'],
            ['name' => 'services.edit', 'category' => 'services', 'action' => 'edit', 'display_name' => 'Editar Serviços', 'description' => 'Permite editar serviços existentes'],
            ['name' => 'services.delete', 'category' => 'services', 'action' => 'delete', 'display_name' => 'Deletar Serviços', 'description' => 'Permite deletar serviços'],

            // Relatórios
            ['name' => 'reports.view', 'category' => 'reports', 'action' => 'view', 'display_name' => 'Visualizar Relatórios', 'description' => 'Permite visualizar relatórios'],
            ['name' => 'reports.export', 'category' => 'reports', 'action' => 'export', 'display_name' => 'Exportar Relatórios', 'description' => 'Permite exportar relatórios'],

            // Configurações
            ['name' => 'settings.view', 'category' => 'settings', 'action' => 'view', 'display_name' => 'Visualizar Configurações', 'description' => 'Permite visualizar configurações'],
            ['name' => 'settings.edit', 'category' => 'settings', 'action' => 'edit', 'display_name' => 'Editar Configurações', 'description' => 'Permite editar configurações'],

            // Notificações
            ['name' => 'notifications.view', 'category' => 'notifications', 'action' => 'view', 'display_name' => 'Visualizar Notificações', 'description' => 'Permite visualizar notificações'],
            ['name' => 'notifications.send', 'category' => 'notifications', 'action' => 'send', 'display_name' => 'Enviar Notificações', 'description' => 'Permite enviar notificações'],

            // Integrações
            ['name' => 'integrations.google_calendar', 'category' => 'integrations', 'action' => 'google_calendar', 'display_name' => 'Google Calendar', 'description' => 'Permite usar integração com Google Calendar'],
            ['name' => 'integrations.whatsapp', 'category' => 'integrations', 'action' => 'whatsapp', 'display_name' => 'WhatsApp', 'description' => 'Permite usar integração com WhatsApp'],
            ['name' => 'integrations.email', 'category' => 'integrations', 'action' => 'email', 'display_name' => 'Email', 'description' => 'Permite usar integração com email'],
        ];

        foreach ($abilities as $abilityData) {
            Ability::create($abilityData);
        }

        // Criar profiles
        $adminProfile = Profile::create([
            'name' => 'admin',
            'display_name' => 'Administrador',
            'description' => 'Acesso total ao sistema',
            'is_active' => true,
        ]);

        $secretaryProfile = Profile::create([
            'name' => 'secretary',
            'display_name' => 'Secretária',
            'description' => 'Acesso limitado para secretárias',
            'is_active' => true,
        ]);

        $clientProfile = Profile::create([
            'name' => 'client',
            'display_name' => 'Cliente',
            'description' => 'Acesso básico para clientes',
            'is_active' => true,
        ]);

        // Atribuir abilities aos profiles
        $adminAbilities = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'companies.view', 'companies.create', 'companies.edit', 'companies.delete',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.delete',
            'appointments.cancel', 'appointments.reschedule',
            'schedules.view', 'schedules.create', 'schedules.edit', 'schedules.delete',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'reports.view', 'reports.export',
            'settings.view', 'settings.edit',
            'notifications.view', 'notifications.send',
            'integrations.google_calendar', 'integrations.whatsapp', 'integrations.email',
        ];

        $secretaryAbilities = [
            'users.view',
            'appointments.view', 'appointments.create', 'appointments.edit', 'appointments.cancel', 'appointments.reschedule',
            'schedules.view',
            'services.view',
            'reports.view',
            'notifications.view', 'notifications.send',
        ];

        $clientAbilities = [
            'appointments.view', 'appointments.create', 'appointments.cancel', 'appointments.reschedule',
            'schedules.view',
            'services.view',
        ];

        // Atribuir abilities
        $adminProfile->abilities()->attach(Ability::whereIn('name', $adminAbilities)->pluck('id'));
        $secretaryProfile->abilities()->attach(Ability::whereIn('name', $secretaryAbilities)->pluck('id'));
        $clientProfile->abilities()->attach(Ability::whereIn('name', $clientAbilities)->pluck('id'));
    }
}
