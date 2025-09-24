<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar perfis usando firstOrCreate para evitar duplicatas
        $profiles = [
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

        foreach ($profiles as $profileData) {
            Profile::firstOrCreate(
                ['name' => $profileData['name']],
                $profileData
            );
        }
    }
}
