<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar profiles
        $adminProfile = Profile::where('name', 'admin')->first();
        $secretaryProfile = Profile::where('name', 'secretary')->first();
        $clientProfile = Profile::where('name', 'client')->first();

        // Criar empresa de exemplo
        $company = Company::create([
            'name' => 'AgendIA Demo',
            'phone' => '(11) 99999-9999',
            'whatsapp_number' => '5511999999999',
            'timezone' => 'America/Sao_Paulo',
        ]);

        // Criar usuário admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@agendia.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 99999-9999',
            'profile_id' => $adminProfile->id,
        ]);

        // Vincular admin à empresa
        $admin->companies()->attach($company->id);

        // Criar usuário secretária
        $secretary = User::create([
            'name' => 'Maria Silva',
            'email' => 'secretaria@agendia.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 88888-8888',
            'profile_id' => $secretaryProfile->id,
        ]);

        // Vincular secretária à empresa
        $secretary->companies()->attach($company->id);

        // Criar usuário cliente
        $client = User::create([
            'name' => 'João Santos',
            'email' => 'cliente@agendia.com',
            'password' => Hash::make('password'),
            'phone' => '(11) 77777-7777',
            'profile_id' => $clientProfile->id,
        ]);

        // Vincular cliente à empresa
        $client->companies()->attach($company->id);

        $this->command->info('Usuários de exemplo criados com sucesso!');
        $this->command->info('Admin: admin@agendia.com / password');
        $this->command->info('Secretária: secretaria@agendia.com / password');
        $this->command->info('Cliente: cliente@agendia.com / password');
    }
}
