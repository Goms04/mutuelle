<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\TraitementEvenement;
use App\Models\TypeEvenement;
use App\Models\User;
use App\Models\UserEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EvenementController extends Controller
{
    //Affichage de la page
    public function show()
    {

        $evenements = Evenement::all();
        return response()->json([
            'code' => 200,
            'message' => 'Données récupérées avec succès',
            'objet' => $evenements
        ]);
    }


    //count prêt en fonction de l'utilisateur connecté: evenements/count
    public function countevenement()
    {
        $user = Auth::user();
        $evenement = Evenement::where('user_id', $user->id)->count();

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $evenement
        ]);
    }

    //userevenements en fonction d'un évènement
    public function showuserev($ref)
    {
        $userev = UserEvenement::where('ref_evenement', $ref)->get();
        $use = UserEvenement::where('ref_evenement', $ref)->firstOrFail();
        $type = $use->typeEvenement_id;
        $typeEv = TypeEvenement::where('id',$type)->firstOrFail();
        $userdata = $userev->map(function ($user) use ($typeEv) {
            return [
                'ref_user_em' => $user->ref_user_em,
                'ref_user_dest' => $user->ref_user_dest,
                'ref_evenement' => $user->ref_evenement,
                'typeEvenement' => $typeEv->lib_type,
                'description' => $user->description,
                'montant' => $user->montant,
                'nom_em' => $user->nom_em,
                'prenom_em' => $user->prenom_em,
                'email_em' => $user->email_em,
                'nom_dest' => $user->nom_dest,
                'prenom_dest' => $user->prenom_dest,
                'email_dest' => $user->email_dest,
                'date' => $user->date,
            ];
        });
        
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $userdata
        ]);
    }

//liste des evenements dont l'index est au niveau directement inférieur à celui de l'utilisateur connecté: /evenements/show
    public function showe()
    {


        $userin = Auth::user()->index;
        $usertr = $userin - 1;

        $ev = Evenement::where('validated', false) // Filtrer les événements non validés
            ->where('index', $usertr)
            ->where('isfinished', false)
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $ev,
        ]);
    }

    public function showobject($ref)
    {
        $evenements = Evenement::where('ref', $ref)->firstOrFail();
        return response()->json([
            'code' => 200,
            'message' => 'Données récupérées avec succès',
            'objet' => $evenements
        ]);
    }

    //Fonction creation
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {

            $user = Auth::user();

            $valid = Validator::make($request->all(), [
                'ref_typeEvenement' => 'required',
                'description' => 'required'
                //'date' => 'required|date',
            ], [
                'ref_typeEvenement.required' => 'Veuillez renseigner le type d\'evenement SVP',
                'description.required' => 'Veuillez decrire l\'evenement en question'
                //'date.required' => 'Veuillez entrer une date SVP',
            ]);

            if ($valid->fails()) {
                return response()->json(['erreur!' => $valid->errors()]);
            }

            $typeEvenement = TypeEvenement::where('ref', $request->input('ref_typeEvenement'))->firstOrFail();

            $evenement = Evenement::create([
                'ref' => Str::uuid(),
                'user_id' => Auth::user()->id,
                'ref_user' => Auth::user()->ref,
                'typeEvenement_id' => $typeEvenement->id,
                'ref_typeEvenement' => $request->input('ref_typeEvenement'),
                'date' => today(),
                'description' => $request->input('description'),
                'validated' => false,
                'nom' => Auth::user()->nom,
                'index' => 0,
                'prenom' => Auth::user()->prenom,
                'email' => Auth::user()->email,
                'montant' => $typeEvenement->montant
            ]);

            TraitementEvenement::create([
                'ref' => Str::uuid(),
                'evenement_id' => $evenement->id,
                'evenement_ref' => $evenement->ref,
                'isfinished' => false,
                'isvalidated' => false,
                'index' => 0,
                'date' => now(),
                'message' => $request->input('message'),
                'traite_par' => $user->id,
            ]);

            DB::commit();
            return response()->json([
                'code' => 500,
                'message' => 'Evènement créé avec succès',
                'objet' => $evenement
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'Erreur' => $e->getMessage()
            ]);
        }
    }

    //fonction modification
    public function update(Request $request, $ref)
    {
        DB::beginTransaction();
        try {

            $valid = Validator::make($request->all(), [
                'ref_typeEvenement' => 'required',
                'description' => 'required'
            ], [
                'ref_typeEvenement.required' => 'Veuillez renseigner le type d\'évènement SVP',
                'description.required' => 'Veuillez décrire l\'évènement en question'
            ]);

            if ($valid->fails()) {
                return response()->json(['Erreur' => $valid->errors()]);
            }


            $typeEvenement = TypeEvenement::where('ref', $request->input('ref_typeEvenement'))->firstOrFail();

            $evenement = Evenement::where('ref', $ref)->firstOrFail();
            $evenement->update([
                'typeEvenement_id' => $typeEvenement->id,
                'description' => $request->input('description'),
                'ref_typeEvenement' => $request->input('ref_typeEvenement'),
                'montant' => $typeEvenement->montant
            ]);

            DB::commit();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'Erreur' => $evenement
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'Erreur' => $e->getMessage()
            ]);
        }
    }

    //*********************************************//
    public function valider($ref)
    {
        /* 
            -   récupérer la liste des users dont l'index eest strictemeent supérieur à 0
            -   vérifier et recupérer depuis la table traitement les traitements d'un évènement
            -   récupérer l'utilisateur connecté
            -   vérifie si son index est le suivant et ensuite vérifier s'il est le dernier
            -   s'il est le suivant et le dernier on exécute la fonction ci-dessous en enregistrant userevenement
            -   s'il est le suivant et pas le dernier on enregistre dans la table traitement 
        */

        DB::beginTransaction();
        try {

            $users = User::where('deleted', false)->get();
            //On récupère l'évènement qu'on veut valider
            $evenement = Evenement::where('ref', $ref)->firstOrFail();
            //On récupère le type d'évènement lié à cet évènement
            $id_typeEvenement = $evenement->typeEvenement_id;
            $typeEvenement = TypeEvenement::where('id', $id_typeEvenement)->first();

            $evenement->update([
                'validated' => true
            ]);

            $nbre = $users->count() - 1;

            //on trouve le montant cotisé par chacun
            $montant_par_tete = $typeEvenement->montant / $nbre;

            foreach ($users as $user) {
                $userEvenement = UserEvenement::create([
                    'ref' => Str::uuid(),
                    'ref_user_em' => $evenement->ref_user,
                    'ref_user_dest' => $user->ref,
                    'user_id' => $user->id,
                    'ref_evenement' => $ref,
                    'evenement_id' => $evenement->id,
                    'ref_typeEvenement' => $typeEvenement->ref,
                    'typeEvenement_id' => $typeEvenement->id,
                    'montant' => $montant_par_tete, //On utilisera un request pour ça par après puisqu'il faut arrondir
                    'nom_em' => $evenement->nom,
                    'prenom_em' => $evenement->prenom,
                    'email_em' => $evenement->email,
                    'description' => $evenement->description,
                    'nom_dest' => $user->nom,
                    'prenom_dest' => $user->prenom,
                    'email_dest' => $user->email,
                    'date' => today(),
                ]);
            }

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Evènement validé avec succès',
                'objet' => $userEvenement
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 200,
                'message' => 'Erreur interne de serveur',
                'Erreur' =>  $e->getMessage()
            ]);
        }
    }

    //fonction de suppression
    public function delete($ref) {}
}
