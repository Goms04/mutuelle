<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create([
            'ref' => Str::uuid(),
            'libelle' => "Administrateur",
        ]);

        Role::create([
            'ref' => Str::uuid(),
            'libelle' => "Validateur",
        ]);

        Role::create([
            'ref' => Str::uuid(),
            'libelle' => "Mutualiste",
        ]);
    }
}
