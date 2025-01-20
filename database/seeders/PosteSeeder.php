<?php

namespace Database\Seeders;

use App\Models\Poste;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\support\Str;

class PosteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Poste::create([
            'ref' => Str::uuid(),
            'libelle' => "Technicien SLF"
        ]);
    }
}
