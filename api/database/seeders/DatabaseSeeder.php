<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeders básicos do sistema
            ProfileSeeder::class,
            AbilitySeeder::class,
            ProfileAbilitySeeder::class,

            // Seeders de dados de referência
            TimezoneSeeder::class,

            // Seeders de dados de exemplo
            CompanySeeder::class,
            UserSeeder::class,
        ]);
    }
}
