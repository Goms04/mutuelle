<?php

namespace App\Http\Controllers;

use App\Models\Poste;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PosteController extends Controller
{
    //

    //Affichage de la page
    public function show()
    {
        $poste = Poste::all();
        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $poste
        ]);
    }

    public function showobject($ref){
        $poste = Poste::where('ref', $ref)->firstOrFail();
        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $poste
        ]);
    }

    //Fonction creation
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {


            $valid = Validator::make($request->all(), [
                'libelle' => 'required',
                'description' => 'required'
            ], [
                'libelle.required' => 'Veuillez entrer un libelle',
                'description.required' => 'Veuillez entrer une description'
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $poste = Poste::create([
                'ref' => Str::uuid(),
                'libelle' => $request->input('libelle'),
                'description' => $request->input('description'),
            ]);
            DB::commit();
            return response()->json([
                'Success' => 'Le Poste a été créée avec succès !',
                'objet' => $poste
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'Stop !' => 'Erreur interne de serveur ',
                'Error' => $e->getMessage()
            ]);
        }
    }

    //fonction modification
    public function update(Request $request, $ref)
    {

        $valid = Validator::make($request->all(), [
            'libelle' => 'required',
            'description' => 'required'
        ], [
            'libelle.required' => 'Veuillez entrer un libelle',
            'description.required' => 'Veuillez entrer une description'
        ]);

        $poste = Poste::where('ref', $ref)->firstOrFail();
        $poste->update($valid->validated());

        if ($valid->fails()) {
            return response()->json(['error' => $valid->errors()]);
        }

        return response()->json([
            'Code' => 200,
            'Message' => 'Poste modifié avec succès',
            'objet'=> $poste
        ]);
    }

    //fonction de suppression
    public function delete($ref)
    {
        $poste = Poste::where('ref', $ref)->firstOrFail();
        $poste->delete($ref);
        return response()->json(['success', 'Poste supprimé avec succès']);
    }
}
