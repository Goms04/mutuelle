<?php

namespace Database\Seeders;

use App\Models\Historique;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HistoriqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $user = User::where('ref', UserSeeder::$userRef)->firstOrFail();

        if($user){
            Historique::create([
            'date' => today(),
            'libelle' => "Création de compte",
            'montant' => 0,
            'user_id' => $user->id,
            'user_ref' => $user->ref,
            'type' => 1,
        ]);
        }
    }
}
