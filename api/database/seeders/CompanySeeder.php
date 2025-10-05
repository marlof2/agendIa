<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Timezone;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar um timezone padrão (Brasil)
        $brazilTimezone = Timezone::where('region', 'Brazil')->first();
        $timezoneId = $brazilTimezone ? $brazilTimezone->id : null;

        $companies = [
            [
                'name' => 'Marques Tech Ltda',
                'person_type' => 'legal',
                'cnpj' => '11222333000144',
                'cpf' => null,
                'responsible_name' => 'Marlo Marques da Silva Filho',
                'phone_1' => '71991717209',
                'has_whatsapp_1' => true,
                'phone_2' => '',
                'has_whatsapp_2' => false,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Maria Consultoria',
                'person_type' => 'physical',
                'cnpj' => null,
                'cpf' => '12345678901',
                'responsible_name' => 'Maria Oliveira Costa',
                'phone_1' => '21988887777',
                'has_whatsapp_1' => true,
                'phone_2' => null,
                'has_whatsapp_2' => false,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Salão da Ana',
                'person_type' => 'physical',
                'cnpj' => null,
                'cpf' => '98765432100',
                'responsible_name' => 'Ana Paula Rodrigues',
                'phone_1' => '31977776666',
                'has_whatsapp_1' => true,
                'phone_2' => '3133332222',
                'has_whatsapp_2' => true,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Clínica Médica São Paulo',
                'person_type' => 'legal',
                'cnpj' => '98765432000110',
                'cpf' => null,
                'responsible_name' => 'Dr. Carlos Eduardo Lima',
                'phone_1' => '1136665555',
                'has_whatsapp_1' => false,
                'phone_2' => '11999991111',
                'has_whatsapp_2' => true,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Barbearia do Zé',
                'person_type' => 'physical',
                'cnpj' => null,
                'cpf' => '45678912345',
                'responsible_name' => 'José Roberto Ferreira',
                'phone_1' => '85955554444',
                'has_whatsapp_1' => true,
                'phone_2' => null,
                'has_whatsapp_2' => false,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Academia Fitness Center',
                'person_type' => 'legal',
                'cnpj' => '11222333000144',
                'cpf' => null,
                'responsible_name' => 'Patricia Mendes Silva',
                'phone_1' => '4734443333',
                'has_whatsapp_1' => true,
                'phone_2' => '47988889999',
                'has_whatsapp_2' => true,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Pet Shop Amigo dos Bichos',
                'person_type' => 'legal',
                'cnpj' => '55666777000188',
                'cpf' => null,
                'responsible_name' => 'Roberto Carlos Santos',
                'phone_1' => '6232221111',
                'has_whatsapp_1' => true,
                'phone_2' => '62977778888',
                'has_whatsapp_2' => false,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Consultório Odontológico',
                'person_type' => 'physical',
                'cnpj' => null,
                'cpf' => '78912345678',
                'responsible_name' => 'Dra. Fernanda Alves',
                'phone_1' => '7131110000',
                'has_whatsapp_1' => false,
                'phone_2' => '71966665555',
                'has_whatsapp_2' => true,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Restaurante Sabor & Arte',
                'person_type' => 'legal',
                'cnpj' => '99888777000166',
                'cpf' => null,
                'responsible_name' => 'Chef Marcelo Pereira',
                'phone_1' => '4130009999',
                'has_whatsapp_1' => true,
                'phone_2' => '41955556666',
                'has_whatsapp_2' => true,
                'timezone_id' => $timezoneId,
            ],
            [
                'name' => 'Consultoria Contábil',
                'person_type' => 'physical',
                'cnpj' => null,
                'cpf' => '32165498732',
                'responsible_name' => 'Contador Ricardo Mendes',
                'phone_1' => '2729998888',
                'has_whatsapp_1' => false,
                'phone_2' => null,
                'has_whatsapp_2' => false,
                'timezone_id' => $timezoneId,
            ],
        ];

        foreach ($companies as $companyData) {
            Company::firstOrCreate(
                ['name' => $companyData['name']],
                $companyData
            );
        }
    }
}
