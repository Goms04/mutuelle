<?php

namespace Database\Seeders;

use App\Models\TypeEvenement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TypeEvenementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TypeEvenement::create([
            'ref' => Str::uuid(),
            'lib_type' => "Naissance d'un enfant",
            'montant' => 60000,
            'deleted' => false
        ]);

        TypeEvenement::create([
            'ref' => Str::uuid(),
            'lib_type' => "Mariage",
            'montant' => 60000,
            'deleted' => false
        ]);


        TypeEvenement::create([
            'ref' => Str::uuid(),
            'lib_type' => "Décès",
            'montant' => 60000,
            'deleted' => false
        ]);
    }
}
