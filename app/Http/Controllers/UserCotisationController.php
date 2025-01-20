<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCotisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserCotisationController extends Controller
{
    //
    public function show(){
        $ucotisation = UserCotisation::all();
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $ucotisation
        ]);
    }


    //Cotisation en fonction de chaque utilisateur
    public function showobject(){
        $user = Auth::user()->ref;
        $ucotisation = UserCotisation::where('ref_user', $user)->get();
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $ucotisation
        ]);
    }

    //A enlever: Somme des cotisations en fonction de l'utilisateur authentifié 
    public function somme(){
         
        // Vérifie si l'utilisateur est authentifié
        if (Auth::check()) {
            Log::info('Utilisateur authentifié', ['user' => Auth::user()]);
        } else {
            Log::warning('Utilisateur non authentifié');
        }
        // Récupère la référence de l'utilisateur authentifié
        $ref_user = Auth::user()->ref;
        // Log de la référence utilisateur
        Log::info('Récupération de la somme pour l\'utilisateur', ['ref_user' => $ref_user]);
        //dd($ref_user);

        // Calcule la somme des cotisations
        $somme = UserCotisation::where('ref_user', $ref_user)->sum('montant_cotise');
        Log::info('Somme calculée', ['somme' => $somme]);

        // Retourne la réponse JSON
        return response()->json([
            'code' => 200,
            'message' => 'Somme obtenue avec succès',
            'objet' => $somme
        ]);
    }


}
