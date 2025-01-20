<?php

namespace App\Http\Controllers;

use App\Models\Pret;
use App\Models\Remboursement;
use App\Models\User;
use App\Models\UserPret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PretController extends Controller
{
    //liste de tous les prêts: /prets/all
    public function showall()
    {
        $user = Auth::user();
        $pret = Pret::all();

        //$user_id = $pret->user_id;
        //$user = User::where('id', $user_id)->get();

        $data = $pret->map(function ($item) /* use ($user) */ {
            return [
                'ref' => $item->ref,
                'nom' => $item->user->nom,
                'prenom' => $item->user->prenom,
                'email' => $item->user->email,
                'montant' => $item->montant,
                'motif_pret' => $item->motif_pret,
                'date_pret' => $item->date_pret,
                'mode_remboursement' => $item->mode_remboursement,
                'montant_remboursement' => $item->montant_remboursement,
                'validated' => $item->validated,
                'isfinished' => $item->isfinished,
                'soldout' => $item->soldout,
                'prerequis' => $item->prerequis,
                'duree' => $item->duree,
                'marge_totale' => $item->marge_totale,
                'quotite_cessible' => $item->quotite_cessible,
                'montant_accorde' => $item->montant_accorde,
                'duree_remboursement_accorde' => $item->duree_remboursement_accorde,
                'index' => $item->index
            ];
        });

        if ($user->index > 0) {
            return response()->json([
                'code' => 200,
                'message' => 'Okay',
                'objet' => $data
            ]);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Vous ne disposez pas des droits pour consulter ceci',
            ]);
        }
    }

    //liste des prêt dont l'index est au niveau directement inférieur à celui de l'utilisateur connecté: /prets/show
    public function showme()
    {
        $userin = Auth::user()->index;
        $usertr = $userin - 1;
        $pret = Pret::where('validated', false) // Filtrer les événements non validés
            ->where('index', $usertr)
            ->where('isfinished', false)
            ->get();

        $data = $pret->map(function ($item) {

            return [
                'ref' => $item->ref,
                'montant' => $item->montant,
                'nom' => $item->user->nom,
                'prenom' => $item->user->prenom,
                'email' => $item->user->email,
                'motif_pret' => $item->motif_pret,
                'date_pret' => $item->date_pret,
                'mode_remboursement' => $item->mode_remboursement,
                'montant_remboursement' => $item->montant_remboursement,
                'validated' => $item->validated,
                'isfinished' => $item->isfinished,
                'soldout' => $item->soldout,
                'prerequis' => $item->prerequis,
                'duree' => $item->duree,
                'marge_totale' => $item->marge_totale,
                'quotite_cessible' => $item->quotite_cessible,
                'montant_accorde' => $item->montant_accorde,
                'duree_remboursement_accorde' => $item->duree_remboursement_accorde,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $data,
        ]);
    }


    //count prêt en fonction de l'utilisateur connecté: prets/count
    public function counpret()
    {
        $user = Auth::user();
        $prets = Pret::where('user_id', $user->id)->count();

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $prets
        ]);
    }


    //Liste de prêts en fonction de l'utilisateur connecté: /prets/
    public function show()
    {
        $user = Auth::user();
        $prets = Pret::where('user_id', $user->id)->get();

        $data = $prets->map(function ($item) {

            return [
                'ref' => $item->ref,
                'montant' => $item->montant,
                'motif_pret' => $item->motif_pret,
                'date_pret' => $item->date_pret,
                'mode_remboursement' => $item->mode_remboursement,
                'montant_accorde' => $item->montant_accorde,
                'montant_remboursement' => $item->montant_remboursement,
                'validated' => $item->validated,
                'isfinished' => $item->isfinished,
                'soldout' => $item->soldout,
                'prerequis' => $item->prerequis,
                'duree' => $item->duree,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $data
        ]);
    }

    //un prêt par rapport à sa référence: /prets/ref
    public function showobject($ref)
    {
        $pret = Pret::where('ref', $ref)->firstOrFail();
        return ([
            'code' => 200,
            'message' => 'Okay',
            'objet' => $pret
        ]);
    }


    //Fonction creation
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {

            //mode_remborsement, 0 pour chèques, 1 pour source
            $pret = Pret::create([
                'ref' => Str::uuid(),
                'montant' => $request->input('montant'),
                'motif_pret' => $request->input('motif'), //
                'date_pret' => today(),
                'mode_remboursement' => $request->input('mode_remboursement'), //boolean
                'montant_remboursement' => $request->input('montant_remboursement'),
                'duree' => $request->input('duree'),
                'user_id' => Auth::user()->id,
            ]);

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt exécuté avec succès',
                'objet' => $pret,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur',
                'Erreur' => $e->getMessage(),
            ]);
        }
    }


    /* public function valider(Request $request, $ref)
    {
        DB::beginTransaction();
        try {
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt exécuté avec succès',
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
    } */

    //fonction modification par l'admin
    public function update(Request $request, $ref)
    {
        DB::beginTransaction();
        try {

            $prets = Pret::where('ref', $ref)->firstOrFail();
            $prets->update([
                'marge_totale' => $request->input('marge_totale'),
                'quotite_cessible' => $request->input('quotite_cessible'),
                'montant_accorde' => $request->input('montant_accorde'),
                'duree_remboursement_accorde' => $request->input('duree_remboursement_accorde'),
                'prerequis' => $request->input('prerequis')
            ]);

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt exécuté avec succès',
                'code' => $prets,
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

    //fonction modification par l'utilisateur
    public function updatebyuser(Request $request, $ref)
    {
        DB::beginTransaction();
        try {

            $valid = Validator::make($request->all(), [

                'montant' => 'required',
                'motif_pret' => 'required',
                'mode_remboursement' => 'required',
                'montant_remboursement' => 'required',
                'duree' => 'required',
            ], [
                'montant.required' => 'le champ montant est obligatoire',
                'motif_pret.required' => 'le champ motif du pret est obligatoire',
                'mode_remboursement.required' => 'le champ mode de remboursement est obligatoire',
                'montant_remboursement.required' => 'le champ montant à remboursement est obligatoire',
                'duree.required' => 'le champ duree est obligatoire',
            ]);

            if ($valid->fails()) {
                return response()->json(['Error' => $valid->errors()]);
            }

            $pret = Pret::where('ref', $ref)->firstOrFail();
            $pret->update($valid->validated());

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Prêt exécuté avec succès',
                'code' => $pret,
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


    //fonction de suppression
    public function delete($id) {}
}
