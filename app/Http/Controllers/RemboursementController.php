<?php

namespace App\Http\Controllers;

use App\Models\Pret;
use App\Models\Remboursement;
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

            $user = Auth::user();

            $prets = Pret::where('ref', $ref)->firstOrFail();

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

            $somme_remboursement = Remboursement::where('pret_id', $prets->id)->sum('montant');
            $aremb = $prets->montant_accorde + ($prets->montant_accorde * 5 / 100);

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
}
