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
            'nom' => "Manou",
            'prenom' => "Gratien",
            'sexe' => 1,
            'date_naissance' => "2000/06/29",
            'email' => "gratien.manou@idstechnologie.com",
            'poste_id' => "1",
            'subdivision_id' => "1",
            'role_id' => "1",
            'montant_a_cotiser' => "5000",
            'index' => 1,
            'password' => Hash::make('Gratien04'),
            'solde_initial' => 200000,
            'enabled' => true
        ]);
        
        
        User::create([
            'ref' => Str::uuid(),
            'nom' => "Toto",
            'prenom' => "Titi",
            'sexe' => 1,
            'date_naissance' => "2002/11/03",
            'email' => "toto.titi@idstechnologie.com",
            'poste_id' => "1",
            'subdivision_id' => "1",
            'role_id' => "2",
            'montant_a_cotiser' => "15000",
            'index' => 2,
            'password' => Hash::make('Titi04'),
            'solde_initial' => 150000,
            'enabled' => true
        ]);
        
        
        User::create([
            'ref' => Str::uuid(),
            'nom' => "Goba",
            'prenom' => "Janvier",
            'sexe' => 1,
            'date_naissance' => "2004/06/29",
            'email' => "janvier.goba@idstechnologie.com",
            'poste_id' => "1",
            'subdivision_id' => "1",
            'role_id' => "2",
            'montant_a_cotiser' => "12000",
            'index' => 3,
            'password' => Hash::make('Janvier04'),
            'solde_initial' => 180000,
            'enabled' => true
        ]);
    }
}
