<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Timezone;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timezones = [
            // Brasil
            ['name' => 'America/Sao_Paulo', 'region' => 'Brasil - São Paulo', 'offset' => 'UTC-3'],
            ['name' => 'America/Manaus', 'region' => 'Brasil - Amazonas', 'offset' => 'UTC-4'],
            ['name' => 'America/Cuiaba', 'region' => 'Brasil - Mato Grosso', 'offset' => 'UTC-4'],
            ['name' => 'America/Campo_Grande', 'region' => 'Brasil - Mato Grosso do Sul', 'offset' => 'UTC-4'],
            ['name' => 'America/Porto_Velho', 'region' => 'Brasil - Rondônia', 'offset' => 'UTC-4'],
            ['name' => 'America/Rio_Branco', 'region' => 'Brasil - Acre', 'offset' => 'UTC-5'],
            ['name' => 'America/Recife', 'region' => 'Brasil - Pernambuco', 'offset' => 'UTC-3'],
            ['name' => 'America/Fortaleza', 'region' => 'Brasil - Ceará', 'offset' => 'UTC-3'],
            ['name' => 'America/Bahia', 'region' => 'Brasil - Bahia', 'offset' => 'UTC-3'],
            ['name' => 'America/Maceio', 'region' => 'Brasil - Alagoas', 'offset' => 'UTC-3'],
            ['name' => 'America/Aracaju', 'region' => 'Brasil - Sergipe', 'offset' => 'UTC-3'],
            ['name' => 'America/Natal', 'region' => 'Brasil - Rio Grande do Norte', 'offset' => 'UTC-3'],
            ['name' => 'America/Joao_Pessoa', 'region' => 'Brasil - Paraíba', 'offset' => 'UTC-3'],
            ['name' => 'America/Sao_Luiz', 'region' => 'Brasil - Maranhão', 'offset' => 'UTC-3'],
            ['name' => 'America/Boa_Vista', 'region' => 'Brasil - Roraima', 'offset' => 'UTC-4'],
            ['name' => 'America/Porto_Velho', 'region' => 'Brasil - Rondônia', 'offset' => 'UTC-4'],
            ['name' => 'America/Santarem', 'region' => 'Brasil - Pará', 'offset' => 'UTC-3'],
            ['name' => 'America/Belem', 'region' => 'Brasil - Pará', 'offset' => 'UTC-3'],
            ['name' => 'America/Macapa', 'region' => 'Brasil - Amapá', 'offset' => 'UTC-3'],
            ['name' => 'America/Palmas', 'region' => 'Brasil - Tocantins', 'offset' => 'UTC-3'],
            ['name' => 'America/Goiania', 'region' => 'Brasil - Goiás', 'offset' => 'UTC-3'],
            ['name' => 'America/Vitoria', 'region' => 'Brasil - Espírito Santo', 'offset' => 'UTC-3'],
            ['name' => 'America/Brasilia', 'region' => 'Brasil - Distrito Federal', 'offset' => 'UTC-3'],
            ['name' => 'America/Montevideo', 'region' => 'Uruguai', 'offset' => 'UTC-3'],
            ['name' => 'America/Argentina/Buenos_Aires', 'region' => 'Argentina', 'offset' => 'UTC-3'],
            ['name' => 'America/Santiago', 'region' => 'Chile', 'offset' => 'UTC-3'],
            ['name' => 'America/Asuncion', 'region' => 'Paraguai', 'offset' => 'UTC-3'],
            ['name' => 'America/Caracas', 'region' => 'Venezuela', 'offset' => 'UTC-4'],
            ['name' => 'America/Bogota', 'region' => 'Colômbia', 'offset' => 'UTC-5'],
            ['name' => 'America/Lima', 'region' => 'Peru', 'offset' => 'UTC-5'],
            ['name' => 'America/La_Paz', 'region' => 'Bolívia', 'offset' => 'UTC-4'],
            ['name' => 'America/Guayaquil', 'region' => 'Equador', 'offset' => 'UTC-5'],
            ['name' => 'America/Guyana', 'region' => 'Guiana', 'offset' => 'UTC-4'],
            ['name' => 'America/Paramaribo', 'region' => 'Suriname', 'offset' => 'UTC-3'],
            ['name' => 'America/Cayenne', 'region' => 'Guiana Francesa', 'offset' => 'UTC-3'],

            // América do Norte
            ['name' => 'America/New_York', 'region' => 'Estados Unidos - Nova York', 'offset' => 'UTC-5'],
            ['name' => 'America/Chicago', 'region' => 'Estados Unidos - Chicago', 'offset' => 'UTC-6'],
            ['name' => 'America/Denver', 'region' => 'Estados Unidos - Denver', 'offset' => 'UTC-7'],
            ['name' => 'America/Los_Angeles', 'region' => 'Estados Unidos - Los Angeles', 'offset' => 'UTC-8'],
            ['name' => 'America/Toronto', 'region' => 'Canadá - Toronto', 'offset' => 'UTC-5'],
            ['name' => 'America/Vancouver', 'region' => 'Canadá - Vancouver', 'offset' => 'UTC-8'],
            ['name' => 'America/Mexico_City', 'region' => 'México', 'offset' => 'UTC-6'],

            // // Europa
            // ['name' => 'Europe/London', 'region' => 'Reino Unido', 'offset' => 'UTC+0'],
            // ['name' => 'Europe/Paris', 'region' => 'França', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Berlin', 'region' => 'Alemanha', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Rome', 'region' => 'Itália', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Madrid', 'region' => 'Espanha', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Amsterdam', 'region' => 'Holanda', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Brussels', 'region' => 'Bélgica', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Zurich', 'region' => 'Suíça', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Vienna', 'region' => 'Áustria', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Prague', 'region' => 'República Tcheca', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Warsaw', 'region' => 'Polônia', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Stockholm', 'region' => 'Suécia', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Oslo', 'region' => 'Noruega', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Copenhagen', 'region' => 'Dinamarca', 'offset' => 'UTC+1'],
            // ['name' => 'Europe/Helsinki', 'region' => 'Finlândia', 'offset' => 'UTC+2'],
            // ['name' => 'Europe/Athens', 'region' => 'Grécia', 'offset' => 'UTC+2'],
            // ['name' => 'Europe/Istanbul', 'region' => 'Turquia', 'offset' => 'UTC+3'],
            // ['name' => 'Europe/Moscow', 'region' => 'Rússia - Moscou', 'offset' => 'UTC+3'],

            // // Ásia
            // ['name' => 'Asia/Tokyo', 'region' => 'Japão', 'offset' => 'UTC+9'],
            // ['name' => 'Asia/Seoul', 'region' => 'Coreia do Sul', 'offset' => 'UTC+9'],
            // ['name' => 'Asia/Shanghai', 'region' => 'China - Xangai', 'offset' => 'UTC+8'],
            // ['name' => 'Asia/Hong_Kong', 'region' => 'Hong Kong', 'offset' => 'UTC+8'],
            // ['name' => 'Asia/Singapore', 'region' => 'Singapura', 'offset' => 'UTC+8'],
            // ['name' => 'Asia/Bangkok', 'region' => 'Tailândia', 'offset' => 'UTC+7'],
            // ['name' => 'Asia/Jakarta', 'region' => 'Indonésia - Jacarta', 'offset' => 'UTC+7'],
            // ['name' => 'Asia/Kolkata', 'region' => 'Índia', 'offset' => 'UTC+5:30'],
            // ['name' => 'Asia/Dubai', 'region' => 'Emirados Árabes Unidos', 'offset' => 'UTC+4'],
            // ['name' => 'Asia/Riyadh', 'region' => 'Arábia Saudita', 'offset' => 'UTC+3'],

            // // Oceania
            // ['name' => 'Australia/Sydney', 'region' => 'Austrália - Sydney', 'offset' => 'UTC+10'],
            // ['name' => 'Australia/Melbourne', 'region' => 'Austrália - Melbourne', 'offset' => 'UTC+10'],
            // ['name' => 'Pacific/Auckland', 'region' => 'Nova Zelândia', 'offset' => 'UTC+12'],

            // // África
            // ['name' => 'Africa/Cairo', 'region' => 'Egito', 'offset' => 'UTC+2'],
            // ['name' => 'Africa/Johannesburg', 'region' => 'África do Sul', 'offset' => 'UTC+2'],
            // ['name' => 'Africa/Lagos', 'region' => 'Nigéria', 'offset' => 'UTC+1'],
            // ['name' => 'Africa/Casablanca', 'region' => 'Marrocos', 'offset' => 'UTC+0'],
        ];

        foreach ($timezones as $timezone) {
            Timezone::firstOrCreate($timezone);
        }
    }
}
