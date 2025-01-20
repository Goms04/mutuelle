<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Historique;
use App\Models\TraitementEvenement;
use App\Models\TypeEvenement;
use App\Models\User;
use App\Models\UserEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery\Generator\StringManipulation\Pass\Pass;

class TraitementEvenementController extends Controller
{
    //



    //liste des traitements d'un evenement: evenements/traitements/ref
    public function showlist($ref)
    {
        $traitement = TraitementEvenement::where('evenement_ref', $ref)->where('index', '>', 0)->get();
        //$evenement = Evenement::where('ref', $ref)->get();

        $data = $traitement->map(function ($item) {
            return [
                'ref' => $item->ref,
                'type_evenement' => optional($item->evenement->typeEvenement)->lib_type,
                'date' => $item->evenement->date,
                'nom' => $item->evenement->nom,
                'prenom' => $item->evenement->prenom,
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






    public function lancer(Request $request, $ref)
    {
        DB::beginTransaction();
        try {
            //récupérer la liste des users dont l'index eest strictement supérieur à 0
            $users = User::where('deleted', false)
                ->where('index', '>', 0)
                ->get();
            // vérifier et recupérer depuis la table traitement les traitements d'un évènement
            $traitement = TraitementEvenement::where('evenement_ref', $ref)->get();
            $evenement = Evenement::where('ref', $ref)->firstOrFail();
            //On récupère le type d'évènement lié à cet évènement
            $id_typeEvenement = $evenement->typeEvenement_id;
            $typeEvenement = TypeEvenement::where('id', $id_typeEvenement)->first();
            //récupérer l'utilisateur connecté
            $user = Auth::user();
            // Vérifiez si l'index de l'utilisateur est supérieur à 0
            if ($user->index > 0) {
                // Récupérez tous les utilisateurs qui ne sont pas supprimés
                $uss = User::where('deleted', false)->get();


                // Trouvez le maximum de l'index parmi les utilisateurs
                $maxIndex = $users->max('index');


                // Vérifiez si l'utilisateur a l'index maximum
                // L'utilisateur a l'index le plus élevé et est donc le dernier

                if ($user->index === $maxIndex) {

                    TraitementEvenement::create([
                        'ref' => Str::uuid(),
                        'evenement_id' => $evenement->id,
                        'evenement_ref' => $ref,
                        'isfinished' => true,
                        'isvalidated' => $request->input('is_valid'),
                        'index' => $user->index,
                        'date' => now(),
                        'message' => $request->input('message'),
                        'traite_par' => $user->id,
                    ]);


                    if ($request->input('is_valid') == false) {

                        $evenement->update([
                            'validated' => false,
                            'isfinished' => true,
                            'index' => $user->index
                        ]);
                    } else {
                        //si is_valid est true

                        $evenement->update([
                            'validated' => true,
                            'isfinished' => true,
                            'index' => $user->index
                        ]);

                        $use = User::where('deleted', false)->count();
                        $nbre = $use - 1;

                        //on trouve le montant cotisé par chacun
                        $montant_par_tete = $typeEvenement->montant / $nbre;

                        foreach ($uss as $u) {

                            if ($evenement->ref_user != $u->ref) {

                                $userEvenement = UserEvenement::create([
                                    'ref' => Str::uuid(),
                                    'ref_user_em' => $evenement->ref_user,
                                    'ref_user_dest' => $u->ref,
                                    'user_id' => $u->id,
                                    'ref_evenement' => $ref,
                                    'evenement_id' => $evenement->id,
                                    'ref_typeEvenement' => $typeEvenement->ref,
                                    'typeEvenement_id' => $typeEvenement->id,
                                    'montant' => $montant_par_tete, //On utilisera un request pour ça par après puisqu'il faut arrondir
                                    'nom_em' => $evenement->nom,
                                    'prenom_em' => $evenement->prenom,
                                    'email_em' => $evenement->email,
                                    'description' => $evenement->description,
                                    'nom_dest' => $u->nom,
                                    'prenom_dest' => $u->prenom,
                                    'email_dest' => $u->email,
                                    'date' => today(),
                                ]);

                                Historique::create([
                                    'date' => today(),
                                    'libelle' => $evenement->description,
                                    'montant' => $montant_par_tete,
                                    'user_id' => $u->id,
                                    'user_ref' => $u->ref,
                                ]);
                            }
                        }
                    }
                } else {
                    // L'utilisateur n'est pas le dernier

                    if ($request->input('is_valid') == false) {
                        TraitementEvenement::create([
                            'ref' => Str::uuid(),
                            'evenement_id' => $evenement->id,
                            'evenement_ref' => $ref,
                            'isfinished' => true,
                            'isvalidated' => $request->input('is_valid'),
                            'index' => $user->index,
                            'message' => $request->input('message'),
                            'date' => now(),
                            'traite_par' => $user->id
                        ]);

                        $evenement->update([
                            'index' => $user->index,
                            'isfinished' => true,
                        ]);
                    } else {

                        TraitementEvenement::create([
                            'ref' => Str::uuid(),
                            'evenement_id' => $evenement->id,
                            'evenement_ref' => $ref,
                            'isfinished' => false,
                            'isvalidated' => $request->input('is_valid'),
                            'index' => $user->index,
                            'message' => $request->input('message'),
                            'date' => now(),
                            'traite_par' => $user->id
                        ]);
                        $evenement->update([
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
                'message' => 'Données récupérées avec succès',
                'objet' => $userEvenement
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'Erreur' => $e->getMessage()
            ]);
        }
    }
}
