<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HistoriqueController extends Controller
{
    //
    public function historique()
    {
        $user_id = Auth::user()->id;
        $historique =  Historique::where('user_id', $user_id)->get();

        $data = $historique->map(function ($item) {
            return [
                'date' => $item->date,
                'type' => $item->type,
                'libelle' => $item->libelle,
                'montant' => $item->montant,
                'user_ref' => $item->user_ref,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $data
        ]);
    }


    public function adminhistorique()
    {
        $userhis = User::where('deleted', false)->where('enabled', true)->get();

        $historique = $userhis->map(function ($user) {

            // Prêts non soldés
            $pretsNonSoldes = $user->pret()->where('soldout', false)
                ->where('isfinished', true)
                ->where('validated', true)
                ->get();
            //dd($pretsNonSoldes);

            // Calcul du débit total : somme des (montant_accorde - somme remboursements)
            $debit = $pretsNonSoldes->sum(function ($pret) {
                $totalRembourse = $pret->remboursement()->sum('montant');
                return 1.05 * ($pret->montant_accorde) - $totalRembourse;
            });


            return [
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'credit' => $user->usercotisation->sum('montant_cotise'),
                'debit' => $debit,
                'solde_actuel' => $user->solde_initial,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $historique
        ]);
    }


    public function histoun()
    {
        $user = Auth::user(); // Récupère l'utilisateur connecté

        // Prêts non soldés
        $pretsNonSoldes = $user->pret()->where('soldout', false)
            ->where('isfinished', true)
            ->where('validated', true)
            ->get();

        // Calcul du débit total
        $debit = $pretsNonSoldes->sum(function ($pret) {
            $totalRembourse = $pret->remboursement()->sum('montant');
            return 1.05 * ($pret->montant_accorde) - $totalRembourse;
        });

        // Tableau final
        $historique = [
            'nom'           => $user->nom,
            'prenom'        => $user->prenom,
            'credit'        => $user->usercotisation->sum('montant_cotise'),
            'debit'         => $debit,
            'solde_actuel'  => $user->solde_initial,
        ];


        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $historique
        ]);
    }


    public function histouser($ref)
    {
        $user = User::where('ref', $ref)->firstOrFail();

        // Prêts non soldés
        $pretsNonSoldes = $user->pret()->where('soldout', false)
            ->where('isfinished', true)
            ->where('validated', true)
        ->get();

        // Calcul du débit total
        $debit = $pretsNonSoldes->sum(function ($pret) {
            $totalRembourse = $pret->remboursement()->sum('montant');
            return 1.05 * ($pret->montant_accorde) - $totalRembourse;
        });

        // Tableau final
        $historique = [
            'nom'           => $user->nom,
            'prenom'        => $user->prenom,
            'credit'        => $user->usercotisation->sum('montant_cotise'),
            'debit'         => $debit,
            'solde_actuel'  => $user->solde_initial,
        ];

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $historique
        ]);
    }

    public function crediter($ref)
    {
        DB::beginTransaction();
        try {
            // Logique pour traiter le cas où le solde est négatif
            $user = User::where('ref', $ref)->firstOrFail();

            $montantRemboursement = abs($user->solde_initial);

            if ($user->solde_initial < 0) {
                $user->update([
                    'solde_initial' => 0,
                ]);

                Historique::create([
                    'date' => today(),
                    'libelle' => 'Régularisation des dûs',
                    'montant' => $montantRemboursement,
                    'user_id' => $user->id,
                    'user_ref' => $user->ref,
                    'type' => true, // Crédit
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Error',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function debiter($ref)
    {
        DB::beginTransaction();
        try {
            // Logique pour traiter le cas où le solde est positif
            $user = User::where('ref', $ref)->firstOrFail();


            if ($user->solde_initial > 0) {

                Historique::create([
                    'date' => today(),
                    'libelle' => 'Régularisation des dûs',
                    'montant' => $user->solde_initial,
                    'user_id' => $user->id,
                    'user_ref' => $user->ref,
                    'type' => false, // Débit
                ]);


                $user->update([
                    'solde_initial' => 0,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Error',
                'error' => $e->getMessage()
            ]);
        }
    }
}
