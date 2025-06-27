<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    
    public static string $userRef;

    public function run(): void
    {
        //

       $user = User::create([
            'ref' => Str::uuid(),
            'nom' => "Plateforme",
            'prenom' => "Mutuelle",
            'sexe' => 1,
            'date_naissance' => "1995/05/15",
            'email' => "mutuelle@idstechnologie.com",
            'role_id' => "1",
            'montant_a_cotiser' => "0",
            'index' => 0,
            'password' =>  Hash::make('Mutuelle@2025'),
            'solde_initial' => 0,
            'enabled' => true
        ]);

        self::$userRef = $user->ref;

        /* User::create([
            'ref' => Str::uuid(),  
            'nom' => "Manou",
            'prenom' => "Gratien",
            'sexe' => 1,
            'date_naissance' => "2000/06/29",
            'email' => "gratien.manou@idstechnologie.com",
            'role_id' => "1",
            'montant_a_cotiser' => "5000",
            'index' => 1,
            'password' => Hash::make('Gratien04'),
            'solde_initial' => 200000,
            'enabled' => true
        ]); */
    }
}
