<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Role;
use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Application::create(['uuid' => '3d924b69-0063-4943-824a-0d5bb445e7ca', 'slug' => 'http://localhost:1234', 'name' => 'Api DEV']);
        Application::create(['uuid' => '22993c21-2e98-454f-a1df-8b8d9c5150c1', 'slug' => 'https://www.geracaoconsciente.pt/api/public', 'name' => 'Api QA']);

        Status::create(['uuid' => Str::uuid(), 'name' => 'active', 'color' => 'success' ]);
        Status::create(['uuid' => Str::uuid(), 'name' => 'registered', 'color' => 'warning' ]);
        Status::create(['uuid' => Str::uuid(), 'name' => 'deleted', 'color' => 'dark' ]);
        Status::create(['uuid' => Str::uuid(), 'name' => 'suspended', 'color' => 'danger' ]);
        Status::create(['uuid' => Str::uuid(), 'name' => 'inactive', 'color' => 'light' ]);

        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Comissão Eleitural', 'code' => 'COMEL', 'description' => 'Ordem na distribuição dos outros Roles', 'color' => 'success']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Administração Partidária', 'code' => 'PLTOP', 'description' => 'Acesso a ferramentas de gestão do partido', 'color' => 'danger']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Comissão Nacional', 'code' => 'COMPT', 'description' => 'Camada para a gestão do nacional', 'color' => 'ancap']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Comissão Districtal', 'code' => 'CDIST', 'description' => 'Camada para a gestão do districto', 'color' => 'gray-600']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Comissão Concelho', 'code' => 'CCONC', 'description' => 'Camada para a gestão do concelho', 'color' => 'gray-400']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Comissão Freguesia', 'code' => 'CFREG', 'description' => 'Camada para a gestão da freguesia', 'color' => 'gray-200']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Libertário', 'code' => 'LIBER', 'description' => 'Camada base para membros PL', 'color' => 'capan']);
        Role::create(['uuid' => Str::uuid(), 'app_id' => 1, 'name' => 'Liberal', 'code' => 'NO-PL', 'description' => 'Camada base para membros registados na plataforma, mas não PL', 'color' => 'primary']);
    }
}
