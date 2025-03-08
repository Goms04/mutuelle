<?php

namespace App\Console\Commands;

use App\Models\Cotisation;
use App\Models\Historique;
use App\Models\User;
use App\Models\UserCotisation;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CreateMonthlyCotisation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-monthly-cotisation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(Request $request)
    {
        

        $cotisation = Cotisation::create([
            'ref' => Str::uuid(),
            'mois' => $request->input('mois'),
            'annee' => $request->input('annee'),
            'isdone' => false
        ]);

        DB::beginTransaction();

        try {
            // Récupérer tous les utilisateurs
            $users = User::all();

            // Créer une cotisation pour chaque utilisateur
            foreach ($users as $user) {
                $userCotisation = UserCotisation::create([
                    'ref' => Str::uuid(),
                    'ref_user' => $user->ref,
                    'ref_cotisation' => $cotisation->ref,
                    'mois' => $cotisation->mois,
                    'annee' => $cotisation->annee,
                    'email' => $user->email,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'montant_cotise' => $user->montant_a_cotiser,
                    'user_id' => $user->id,
                    'cotisation_id' => $cotisation->id,
                ]);

                Historique::create([
                    'date' => today(),
                    'libelle' => 'cotisation mensuelle',
                    'montant' => $user->montant_a_cotiser,
                    'user_id' => $user->id,
                    'user_ref' => $user->ref,
                ]);
            }

            $cotisation->update([
                'isdone' => true
            ]);

            DB::commit();
            $this->info('Cotisation mensuelle effectuée avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erreur interne de serveur : ' . $e->getMessage());
        }
    }
}
