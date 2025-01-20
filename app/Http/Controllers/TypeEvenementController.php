<?php

namespace App\Http\Controllers;

use App\Models\TypeEvenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class TypeEvenementController extends Controller
{
    //
    public function show() {
        $types = TypeEvenement::all();
        return response()->json([
            'code' => 200,
            'Message' => 'Okay',
            'objet' => $types
        ]);
    }

    public function showobject($ref){
        $types = TypeEvenement::where('ref', $ref)->firstOrFail();
        return response()->json([
            'code' => 200,
            'Message' => 'Okay',
            'objet' => $types
        ]);
    }


    public function create(Request $request)
    {

        DB::beginTransaction();

        try {

            $valid = Validator::make($request->all(), [
                'lib_type' => 'required',
                'montant' => 'required|numeric',
            ], [
                'lib_type.required' => 'Le type d\'évènement doit être obligatoire',
                'montant.required' => 'Le montant est obligatoire',
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $types = TypeEvenement::create([
                'ref' => Str::uuid(),
                'lib_type' => $request->input('lib_type'),
                'montant' => $request->input('montant'),
            ]);
            
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Type d\'évènement créé avec succès! ',
                'objet' => $types
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur ',
                'Erreur' => $e->getMessage()
            ]);
        }
    }


    public function update(Request $request, $ref)
    {

        DB::beginTransaction();

        try {

            $valid = Validator::make($request->all(), [
                'lib_type' => 'required',
                'montant' => 'required|numeric',
            ], [
                'lib_type.required' => 'Le type d\'évènement doit être obligatoire',
                'montant.required' => 'Le montant est obligatoire',
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $types = TypeEvenement::where('ref', $ref)->firstOrFail();
            $types->update($valid->validated());

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Type d\'évènement modifié avec succès! ',
                'objet' => $types
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur ',
                'Erreur' => $e->getMessage()
            ]);
        }
    }


    public function delete($ref)
    {
        try{

            $types = TypeEvenement::where('ref', $ref)->firstOrFail();
            $types->delete($ref);

            return response()->json([
                'code' => 200,
                'message' => 'Type d\'évènement supprimé avec succès! ',
            ]);

        }catch(\Exception $e){
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur ',
                'Erreur' => $e->getMessage()
            ]);
        }
        
    }
}
