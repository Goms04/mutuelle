<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use App\Models\Pret;
use App\Models\Remboursement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class RemboursementController extends Controller
{
    //

    //get 1 remboursement: /remboursements/ref
    public function getone($ref)
    {
        $remb = Remboursement::where('ref', $ref)->firstOrFail();

        $data = $remb->map(function ($item) {
            return [
                'ref' => $item->ref,
                'pret_ref' => $item->pret_ref,
                'date_remboursement' => $item->date_remboursement,
                'montant' => $item->montant,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $data,
        ]);
    }


    //get tous les remboursements: /remboursements/
    public function getall()
    {
        $remb = Remboursement::all();

        $data = $remb->map(function ($item) {
            return [
                'ref' => $item->ref,
                'pret_ref' => $item->pret_ref,
                'date_remboursement' => $item->date_remboursement,
                'montant' => $item->montant,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $data,
        ]);
    }


    //get tous remboursement en fonction du pret: /remboursements/pret/ref
    public function getrp($ref)
    {
        $remb =  Remboursement::where('pret_ref', $ref)->get();

        $data = $remb->map(function ($item) {
            return [
                'ref' => $item->ref,
                'pret_ref' => $item->pret_ref,
                'date_remboursement' => $item->date_remboursement,
                'montant' => $item->montant,
                'email' => $item->email,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $data,
        ]);
    }


    //Fonction gérant le remboursement : manuel
    public function rembourser(Request $request, $ref)
    {
        DB::beginTransaction();
        try {

            //$user = Auth::user();

            $user = User::where('ref', $ref)->firstOrFail();
            $prets = Pret::where('soldout', false)
                ->where('isfinished', true)
                ->where('validated', true)
                ->where('user_id', $user->id)
            ->get();

            foreach ($prets as $pret) {
                $somme_remboursement = Remboursement::where('pret_id', $pret->id)->sum('montant');
                $pourcentage = $pret->montant_accorde * 0.05;
                $montant_restant = $pret->montant_accorde - $somme_remboursement;

                $remboursement = Remboursement::create([
                    'ref' => Str::uuid(),
                    'date_remboursement' => today(),
                    'montant' => $montant_restant,
                    'pret_id' => $pret->id,
                    'pret_ref' => $pret->ref,
                    'user_id' => $user->id,
                    'ref_user' => $user->ref,
                    'email' => $user->email,
                ]);

                Historique::create([
                    'date' => today(),
                    'libelle' => 'Rembousement mensuel manuel',
                    'montant' => $montant_restant,
                    'user_id' => $user->id,
                    'user_ref' => $user->ref,
                    'type' => true
                ]);

                $user_system = User::where('id', 1)->firstOrFail();

                $user_system->update([
                    'solde_initial' => $user_system->solde_initial + $pourcentage,
                ]);

                $user->update([
                    'solde_initial' => $user->solde_initial + $remboursement->montant
                ]);


                $pret->update([
                    'soldout' => true
                ]);
            }

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt exécuté avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'code' => 'Erreur interne de serveur',
                'Erreur' => $e->getMessage(),
            ]);
        }
    }



    //Remboursement automatique
    public function rembourauto()
    {
        DB::beginTransaction();
        try {

            //$user = Auth::user();

            //on récupère tous les prêts non soldés
            $pret = Pret::where('soldout', false)
                ->where('isfinished', true)
                ->where('validated', true)
                ->get();

            //on va parcourir les prêts
            foreach ($pret as $p) {
                //$mont = $p->montant_remboursement;
                $user_id = $p->user_id; //on récupère l'id de l'utilisateur ayant émis le prêt

                $use = User::where('id', $user_id)->firstOrFail(); //on récupère les détails de l'utilisateur



                $somme_remboursement = Remboursement::where('pret_id', $p->id)->sum('montant'); //somme des remboursements déjà payés en fonction de chaque prêt
                $aremb = $p->montant_accorde + ($p->montant_accorde * 5 / 100); //montant emprunté + 5%



                //on va essayer de compter le nbre de mois de remboursement d'un prêt
                $cremb = Remboursement::where('pret_id', $p->id)->count();

                //nbre de mois restants pour finaliser le payement
                $moisrest = $p->nbmois_remboursement - $cremb;

                //réel montant à rembourser par mois //total à rembourser - la somme des remboursement efffectués(y compris les manuels entrés) / le nombre de mois restants
                $montantparmois = ($aremb - $somme_remboursement) / $moisrest;

                //si somme des remboursements inférieur au montant à rembourser(montant avec les 5%)
                if ($somme_remboursement < $aremb) {
                    // prochain payement
                    if ($somme_remboursement + $montantparmois >= $aremb) {
                        //debordement
                        $montant_restant = $aremb - $somme_remboursement;

                        $remboursement = Remboursement::create([
                            'ref' => Str::uuid(),
                            'date_remboursement' => today(),
                            'montant' => $montant_restant,
                            'pret_id' => $p->id,
                            'pret_ref' => $p->ref,
                            'user_id' => $use->id,
                            'ref_user' => $use->ref,
                            'email' => $use->email,
                        ]);

                        Historique::create([
                            'date' => today(),
                            'libelle' => 'Rembousement automatique',
                            'montant' => $montant_restant,
                            'user_id' => $use->id,
                            'user_ref' => $use->ref,
                            'type' => true //Crédit et debit pour false
                        ]);

                        // sys
                        $user_system = User::where('id', 1)->firstOrFail();

                        //on donne au système les 5%
                        $user_system->update([
                            'solde_initial' => $user_system->solde_initial + ($aremb - $p->montant_accorde)  // 5%
                        ]);

                        //l'utilisateur ayant émi le prêt se voit modifier son solde_initial
                        $use->update([
                            'solde_initial' => $use->solde_initial + ($montant_restant - ($aremb - $p->montant_accorde))
                        ]);

                        $p->update([
                            'soldout' => true
                        ]);
                    } else {
                        //payement normal
                        $remboursement = Remboursement::create([
                            'ref' => Str::uuid(),
                            'date_remboursement' => today(),
                            'montant' => $montantparmois,
                            'pret_id' => $p->id,
                            'pret_ref' => $p->ref,
                            'user_id' => $use->id,
                            'ref_user' => $use->ref,
                            'email' => $use->email,
                        ]);


                        Historique::create([
                            'date' => today(),
                            'libelle' => 'Rembousement automatique',
                            'montant' => $montantparmois,
                            'user_id' => $use->id,
                            'user_ref' => $use->ref,
                            'type' => true //Crédit et debit pour false
                        ]);

                        $use->update([
                            'solde_initial' => $use->solde_initial + $remboursement->montant
                        ]);
                    }
                } else {
                    //soldé
                    $p->update([
                        'soldout' => true
                    ]);
                }


                /* if ($somme_remboursement >= $aremb) {
                    $p->update([
                        'soldout' => true
                    ]);
                } */
            }


            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Remboursement effectué avec succès',
                'objet' => 'Okay'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'code' => 'Erreur interne de serveur',
                'Erreur' => $e->getMessage(),
            ]);
        }
    }

    public function rembourautomatique()
    {
        DB::beginTransaction();
        try {

            // Récupérer tous les prêts non soldés
            $prets = Pret::where('soldout', false)
                ->where('isfinished', true)
                ->where('validated', true)
                ->get();

            //dd($prets);

            foreach ($prets as $pret) {
                $user = User::findOrFail($pret->user_id);

                // Somme des remboursements déjà effectués pour ce prêt
                $sommeRemboursement = Remboursement::where('pret_id', $pret->id)->sum('montant');

                // Montant total à rembourser = montant accordé + 5% d'intérêt
                $montantTotalARembourser = $pret->montant_accorde * 1.05;

                // Nombre de remboursements déjà effectués
                $nombreRemboursementsEffectues = Remboursement::where('pret_id', $pret->id)->count();

                // Nombre de mois restants pour rembourser
                $moisRestants = $pret->nbmois_remboursement - $nombreRemboursementsEffectues;

                // Si aucun mois restant, on considère que le prêt est soldé
                if ($moisRestants <= 0) {
                    $pret->update(['soldout' => true]);
                    continue;
                }

                // Calcul du montant à rembourser par mois (reste à rembourser divisé par mois restants)
                $montantParMois = ($montantTotalARembourser - $sommeRemboursement) / $moisRestants;

                // Si le prêt est déjà soldé (cas rare mais sécuritaire)
                if ($sommeRemboursement >= $montantTotalARembourser) {
                    $pret->update(['soldout' => true]);
                    continue;
                }

                // Vérifier si le prochain remboursement dépasse ou atteint le montant total à rembourser
                if ($sommeRemboursement + $montantParMois >= $montantTotalARembourser) {
                    // Calcul du montant restant à rembourser (dernier paiement)
                    $montantRestant = $montantTotalARembourser - $sommeRemboursement;

                    // Création du remboursement final
                    $remboursement = Remboursement::create([
                        'ref' => Str::uuid(),
                        'date_remboursement' => today(),
                        'montant' => $montantRestant,
                        'pret_id' => $pret->id,
                        'pret_ref' => $pret->ref,
                        'user_id' => $user->id,
                        'ref_user' => $user->ref,
                        'email' => $user->email,
                    ]);

                    Historique::create([
                        'date' => today(),
                        'libelle' => 'Remboursement automatique',
                        'montant' => $montantRestant,
                        'user_id' => $user->id,
                        'user_ref' => $user->ref,
                        'type' => true, // Crédit
                    ]);

                    // Mise à jour du solde du système (user_id = 1)
                    $userSystem = User::findOrFail(1);
                    $interet = $montantTotalARembourser - $pret->montant_accorde;

                    $userSystem->update([
                        'solde_initial' => $userSystem->solde_initial + $interet,
                    ]);

                    // Mise à jour du solde de l'utilisateur (montant remboursé net des intérêts)
                    $user->update([
                        'solde_initial' => $user->solde_initial + ($montantRestant - $interet),
                    ]);

                    // Marquer le prêt comme soldé
                    $pret->update(['soldout' => true]);
                } else {
                    // Paiement normal (pas le dernier)
                    $remboursement = Remboursement::create([
                        'ref' => Str::uuid(),
                        'date_remboursement' => today(),
                        'montant' => $montantParMois,
                        'pret_id' => $pret->id,
                        'pret_ref' => $pret->ref,
                        'user_id' => $user->id,
                        'ref_user' => $user->ref,
                        'email' => $user->email,
                    ]);

                    Historique::create([
                        'date' => today(),
                        'libelle' => 'Remboursement automatique',
                        'montant' => $montantParMois,
                        'user_id' => $user->id,
                        'user_ref' => $user->ref,
                        'type' => true,
                    ]);

                    // Mise à jour du solde de l'utilisateur
                    $user->update([
                        'solde_initial' => $user->solde_initial + $montantParMois,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => 'Remboursement effectué avec succès',
                'objet' => 'Okay',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'erreur' => $e->getMessage(),
            ]);
        }
    }
}
