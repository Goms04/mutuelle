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
    public function run(): void
    {
        //
        User::create([
            'ref' => Str::uuid(),
            'nom' => "Platforme",
            'prenom' => "sys",
            'sexe' => 1,
            'date_naissance' => "1995/05/15",
            'email' => "plateforme@idstechnologie.com",
            'role_id' => "1",
            'montant_a_cotiser' => "5000",
            'index' => 1,
            'password' =>  Str::uuid(),
            'solde_initial' => 0,
            'enabled' => false
        ]);

        User::create([
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
        ]);
    }
}
