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
