<?php

namespace App\Http\Controllers;

use App\Models\Historique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
