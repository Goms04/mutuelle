<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use App\Models\Pret;
use App\Models\TraitementPret;
use App\Models\User;
use App\Models\UserPret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class TraitementPretController extends Controller
{


    //liste des traitements d'un prêt: prets/traitements/ref
    public function showlist($ref)
    {
        $traitement = TraitementPret::where('pret_ref', $ref)->where('index', '>', 0)->get();

        $data = $traitement->map(function ($item) {
            return [
                'ref' => $item->ref,
                'date' => $item->pret->date,
                'nom' => $item->pret->user->nom,
                'prenom' => $item->pret->user->prenom,
                'is_valid' => $item->isvalidated,
                'traite_par' => $item->user->nom . ' ' . $item->user->prenom,
                'traite_le' => $item->date,
                'message' => $item->message,
            ];
        });


        return response()->json([
            'code' => 200,
            'message' => 'Traitements reçus avec succès',
            'objet' => $data
        ]);
    }

    //
    public function valider(Request $request, $ref)
    {
        DB::beginTransaction();
        try {

            $user = Auth::user();
            $pret = Pret::where('ref', $ref)->firstOrFail();
            $users = User::where('deleted', false)
                ->where('enabled', true)
                ->get();


            // Vérifiez si l'index de l'utilisateur est supérieur à 0
            if ($user->index > 0) {

                // Récupérez tous les utilisateurs qui ne sont pas supprimés
                $uss = User::where('deleted', false)
                    ->where('enabled', true)
                    ->get();
                // Trouvez le maximum de l'index parmi les utilisateurs
                $maxIndex = $uss->max('index');

                // Vérifiez si l'utilisateur a l'index maximum
                if ($user->index === $maxIndex) {
                    // L'utilisateur a l'index le plus élevé et est donc le dernier
                    TraitementPret::create([
                        'ref' => Str::uuid(),
                        'pret_id' => $pret->id,
                        'pret_ref' => $ref,
                        'isfinished' => true,
                        'isvalidated' => $request->input('is_valid'),
                        'index' => $user->index,
                        'message' => $request->input('message'),
                        'date' => now(),
                        'traite_par' => $user->id
                    ]);

                    if ($request->input('is_valid') == false) {

                        $pret->update([
                            'validated' => false,
                            'isfinished' => true,
                            'index' => $user->index
                        ]);
                    } else {

                        UserPret::create([
                            'ref' => Str::uuid(),
                            'user_ref' => $pret->user->ref,
                            'user_id' => $pret->user->id,
                            'motif' => $pret->motif_pret,
                            'montant' => $pret->montant_accorde,
                            'nom' => $pret->user->nom,
                            'prenom' => $pret->user->prenom,
                            'email' => $pret->user->email,
                        ]);

                        $pret->update([
                            'validated' => true,
                            'isfinished' => true,
                            'index' => $user->index
                        ]);

                        //solde de l'utilisateur qui demandé le prêt - le montant qui lui a été accordé
                        $us = User::where('id', $pret->user_id)->firstOrFail();
                        $us->update([
                            'solde_initial' => $us->solde_initial - $pret->montant_accorde
                        ]);


                        Historique::create([
                            'date' => today(),
                            'libelle' => $pret->motif_pret,
                            'type' => false,
                            'montant' => $pret->montant_accorde,
                            'user_id' => $pret->user->id,
                            'user_ref' => $pret->user->ref,
                        ]);
                    }
                } else {
                    //si l'utilisateur n'est pas le dernier
                    if ($request->input('is_valid') == false) {
                        //s'il ne valide pas
                        $trait = TraitementPret::create([
                            'ref' => Str::uuid(),
                            'pret_id' => $pret->id,
                            'pret_ref' => $ref,
                            'isfinished' => true,
                            'isvalidated' => $request->input('is_valid'),
                            'index' => $user->index,
                            'message' => $request->input('message'),
                            'date' => now(),
                            'traite_par' => $user->id
                        ]);

                        $pret->update([
                            'index' => $user->index,
                            'isfinished' => true,
                        ]);
                    } else {
                        //s'il valide 
                        $trait = TraitementPret::create([
                            'ref' => Str::uuid(),
                            'pret_id' => $pret->id,
                            'pret_ref' => $ref,
                            'isfinished' => false,
                            'isvalidated' => $request->input('is_valid'),
                            'index' => $user->index,
                            'message' => $request->input('message'),
                            'date' => now(),
                            'traite_par' => $user->id
                        ]);
                        $pret->update([
                            'index' => $user->index,
                        ]);
                    }
                }
            } else {
                // L'index de l'utilisateur n'est pas supérieur à 0
            }


            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt validé avec succès',
                'code' => $request,
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
