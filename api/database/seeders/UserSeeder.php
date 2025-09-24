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
            'phone' => '11999999999',
            'whatsapp_number' => '11999999999',
            'timezone' => 'America/Sao_Paulo',
        ]);

        // Criar usuário admin
        $admin = User::create([
            'name' => 'Marlo Marques da Silva Filho',
            'email' => 'marlosilva.f2@gmail.com',
            'password' => Hash::make('123456'),
            'phone' => '71991717209',
            'profile_id' => $adminProfile->id,
        ]);

        // Vincular admin à empresa
        $admin->companies()->attach($company->id);

        // Criar usuário secretária
        $secretary = User::create([
            'name' => 'Maria Silva',
            'email' => 'secretaria@agendia.com',
            'password' => Hash::make('password'),
            'phone' => '11888888888',
            'profile_id' => $secretaryProfile->id,
        ]);

        // Vincular secretária à empresa
        $secretary->companies()->attach($company->id);

        // Criar usuário cliente
        $client = User::create([
            'name' => 'João Santos',
            'email' => 'cliente@agendia.com',
            'password' => Hash::make('password'),
            'phone' => '11777777777',
            'profile_id' => $clientProfile->id,
        ]);

        // Vincular cliente à empresa
        $client->companies()->attach($company->id);

        $this->command->info('Usuários de exemplo criados com sucesso!');
        $this->command->info('Admin: marlosilva.f2@gmail.com / 123');
        $this->command->info('Secretária: secretaria@agendia.com / password');
        $this->command->info('Cliente: cliente@agendia.com / password');
    }
}
