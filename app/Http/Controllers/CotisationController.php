<?php

namespace App\Http\Controllers;

use App\Models\Cotisation;
use App\Models\Historique;
use App\Models\User;
use App\Models\UserCotisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CotisationController extends Controller
{


    public function show()
    {
        $cotisation = Cotisation::all();
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $cotisation
        ]);
    }




    public function showobject($ref)
    {
        $cotisation = Cotisation::where('ref', $ref)->firstOrFail();
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $cotisation
        ]);
    }

   /*  public function createco(Request $request)
    {
        DB::beginTransaction();
        try {

            $valid = Validator::make($request->all(), [
                'mois' => 'required|integer',
                'annee' => 'required|integer|between:1900,9999'
            ], [
                'mois.required' => 'Le mois à entrer est obligatoire',
                'annee.required' => 'Veuillez entrer une année conforme: entre 1900 et 9999'
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $cotisation = Cotisation::create([
                'ref' => Str::uuid(),
                'mois' => $request->input('mois'),
                'annee' => $request->input('annee'),
                'isdone' => false
            ]);
            DB::commit();
            return response()->json([
                'code' => 200,
                'messsage' => 'Cotisation créée avec succès!',
                'objet' => $cotisation
            ]);
        } catch (\exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'Erreur!' => $e->getMessage()
            ]);
        }
    } */


    //fonction qui lance la cotisation mensuelle
    public function cotisation(Request $request)
    {
        DB::beginTransaction();

        try {

            $cotisation = Cotisation::create([
                'ref' => Str::uuid(),
                'mois' => now()->format('m'),
                'annee' => now()->format('Y'),
                'isdone' => false
            ]);

            // Récupérer tous les utilisateurs
            $users = User::all();

            // Créer une cotisation pour chaque utilisateur
            foreach ($users as $user) {
                $usercotisation = UserCotisation::create([
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

                $user->update([
                    'solde_initial' => $user->solde_initial + $user->montant_a_cotiser,
                ]);

            }

            $cotisation->update([
                'isdone' => true
            ]);

            DB::commit();
            return response()->json([
                'code' => '200',
                'message' => 'Cotisation mensuelle efectuée avec succès',
                'objet' => $usercotisation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => '500',
                'message' => 'Erreur interne de serveur',
                'objet' => $e->getMessage(),
            ]);
        }
    }


    public function update(Request $request, $ref)
    {
        DB::beginTransaction();

        try {

            $valid = Validator::make($request->all(), [
                'mois' => 'required|integer',
                'annee' => 'required|integer|between:1900,9999'
            ], [
                'mois.required' => 'Le mois à entrer est obligatoire',
                'annee.required' => 'Veuillez entrer une année conforme: entre 1900 et 9999'
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $cotisation = Cotisation::where('ref', $ref)->firstOrFail();
            $cotisation->update($valid->validated());

            DB::commit();
            return response()->json([
                'code' => '200',
                'message' => 'Cotisation mensuelle efectuée avec succès',
                'objet' => $cotisation,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => '500',
                'message' => 'Erreur interne de serveur',
                'objet' => $e->getMessage(),
            ]);
        }
    }
}
