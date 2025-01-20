<?php

namespace App\Http\Controllers;

use App\Models\Subdivision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SubdivisionController extends Controller
{
    //Affichage de la page
    public function show()
    {
        $subdivision = Subdivision::all();
        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $subdivision
        ]);
    }

    public function showobject($ref){
        $subdivision = Subdivision::where('ref', $ref)->firstOrFail();
        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $subdivision
        ]);
    }

    //Fonction creation
    public function create(Request $request)
    {

        DB::beginTransaction();
        try {

            $valid = Validator::make($request->all(), [
                'libelle' => 'required',
                'description' => 'string|nullable'
            ], [
                'libelle.required' => 'Veuillez entrer un libelle'
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $subdivision = Subdivision::create([
                'ref' => Str::uuid(),
                'libelle' => $request->input('libelle'),
                'description' => $request->input('description')
            ]);

            DB::commit();
            return response()->json([
                'code' => 200,
                'Success' => 'La subdivision a été créée avec succès !',
                'objet' => $subdivision
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'message' => $e->getMessage()
            ]);
        }
    }

    //fonction modification
    public function update(Request $request, $ref)
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

            $subdivision = Subdivision::where('ref', $ref)->firstOrFail();
            $subdivision->update($valid->validated());

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Subdivision modifiée avec succès',
                'objet' => $subdivision
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'message' => $e->getMessage()
            ]);
        }
    }

    //fonction de suppression
    public function delete($ref)
    {
        DB::beginTransaction();

        try {

            $sub = Subdivision::where('ref', $ref)->firstOrFail();
            $sub->delete($ref);
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Subdivision Supprimée avec succès',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'message' => $e->getMessage()
            ]);

        }
    }
}
