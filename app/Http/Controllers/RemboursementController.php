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


    //Fonction gérant le remboursement
    public function rembourser(Request $request, $ref)
    {
        DB::beginTransaction();
        try {

            //$user = Auth::user();

            $prets = Pret::where('ref', $ref)->firstOrFail();
            $user = User::where('id', $prets->user_id)->firstOrFail();



            $remboursement = Remboursement::create([
                'ref' => Str::uuid(),
                'date_remboursement' => today(),
                'montant' => $request->input('montant'),
                'pret_id' => $prets->id,
                'pret_ref' => $ref,
                'user_id' => $user->id,
                'ref_user' => $user->ref,
                'email' => $user->email,
            ]);


            Historique::create([
                'date' => today(),
                'libelle' => 'Rembousement mensuelle',
                'montant' => $request->input('montant'),
                'user_id' => $user->id,
                'user_ref' => $user->ref,
                'type' => true
            ]);



            $somme_remboursement = Remboursement::where('pret_id', $prets->id)->sum('montant');
            $aremb = $prets->montant_accorde + ($prets->montant_accorde * 5 / 100);

            if ($somme_remboursement > $prets->montant_accorde) {

                $user_system = User::where('id', 1)->firstOrFail();

                $user_system->update([
                    'solde_initial' => $user_system->solde_initial + ($aremb - $prets->montant_accorde)
                ]);

                $user->update([
                    'solde_initial' => $user->solde_initial + $remboursement->montant
                ]);
            } else {
                $user->update([
                    'solde_initial' => $user->solde_initial + $remboursement->montant
                ]);
            }

            if ($somme_remboursement >= $aremb) {
                $prets->update([
                    'soldout' => true
                ]);
            }


            $data = $remboursement->map(function ($item) use ($ref) {
                return [
                    'pret_ref' => $ref,
                    'montant' => $item->montant,
                ];
            });

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt exécuté avec succès',
                'code' => $data,
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



            $pret = Pret::where('soldout', false)->get();


            foreach ($pret as $p) {
                //$mont = $p->montant_remboursement;
                $user_id = $p->user_id;

                $use = User::where('id', $user_id)->firstOrFail();

                /* $use->update([
                    'solde_initial' => $use->solde_initial + $p->montant_remboursement
                ]); */



                $somme_remboursement = Remboursement::where('pret_id', $p->id)->sum('montant'); //somme des remboursements déjà payés
                $aremb = $p->montant_accorde + ($p->montant_accorde * 5 / 100); //montant emprunté + 5%



                //$montantparmois = $aremb - $somme_remboursement / $p->nbmois_remboursement;

                //on va essayer de compter le nbre de mois de remboursement d'un prêt
                $cremb = Remboursement::where('pret_id', $p->id)->count();

                //nbre de mois restants pour finaliser le payement
                $moisrest = $p->nbmois_remboursement - $cremb;

                //réel montant à rembourser par mois //total à rembourser - la somme des remboursement efffectués(y compris les manuels entrés) / le nombre de mois restants
                $montantparmois = $aremb - $somme_remboursement / $moisrest;


                //si somme des remboursements inférieur au montant à rembourser(montant avec les 5%)
                if($somme_remboursement < $aremb){
                    // prochain payement
                    if($somme_remboursement + $montantparmois >= $aremb){
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

                    }else{
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

                }else{
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
}
