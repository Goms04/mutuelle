<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Pret;
use App\Models\User;
use App\Models\UserCotisation;
use App\Models\UserEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function npret()
    {
        $user_id = Auth::user()->id;
        $nb = Pret::where('user_id', $user_id)->count();
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $nb
        ]);
    }

    public function npretatt()
    {
        $userin = Auth::user()->index;
        $usertr = $userin - 1;
        $pret = Pret::where('validated', false) // Filtrer les événements non validés
            ->where('index', $usertr)
            ->where('isfinished', false)
            ->count();


        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $pret,
        ]);
    }

    public function nev()
    {
        $user_id = Auth::user()->id;
        $nb = Evenement::where('user_id', $user_id)->count();
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $nb
        ]);
    }


    public function nevatt()
    {
        $userin = Auth::user()->index;
        $usertr = $userin - 1;

        $ev = Evenement::where('validated', false) // Filtrer les événements non validés
            ->where('index', $usertr)
            ->where('isfinished', false)
            ->count();

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $ev,
        ]);
    }

    //solde total mutuel
    public function solde()
    {

        $solde_total = User::where('deleted',false)->sum('solde_initial');
        //$cotisation = UserCotisation::sum('montant_cotise');
        //$evenement = UserEvenement::sum('montant');

        //$solde_total = $solde_user + $cotisation - $evenement;
        //dd($solde_total);
        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $solde_total,
        ]);
    }


    //Solde individuel
    public function soldeind()
    {
        $user = Auth::user();
        $montant = $user->solde_initial;
        /* $mont = UserCotisation::where('ref_user', $user_ref)->sum('montant_cotise');
        $ev = UserEvenement::where('ref_user_dest', $user_ref)->sum('montant');

        $montant = $user->solde_initial + $mont - $ev; */

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $montant,
        ]);
    }
}
