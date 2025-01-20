<?php

namespace Database\Seeders;

use App\Models\Subdivision;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\support\Str;

class SubdivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Subdivision::create([
            'ref' => Str::uuid(),
            'libelle' => "SLF",
            'Description' => "Service Logiciel & Formations"
        ]);
    }
}
