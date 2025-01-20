<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    //


    //Affichage de la page
    public function show()
    {
        $role = Role::all();
        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $role
        ]);
    }

    public function showobject($ref){
        $role = Role::where('ref', $ref)->firstOrFail();
        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $role
        ]);
    }

    //Fonction creation
    public function create(Request $request)
    {

        DB::beginTransaction();
        try {


            $valid = Validator::make($request->all(), [
                'libelle' => 'required',
            ], [
                'libelle.required' => 'Veuillez entrer un libelle'
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }

            $role = Role::create([
                'ref' => Str::uuid(),
                'libelle' => $request->input('libelle'),
            ]);
            DB::commit();
            return response()->json([
                'Success' => 'Le Role a été créée avec succès !',
                'objet' => $role
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

        DB::beginTransaction();

        try {

            $valid = Validator::make($request->all(), [
                'libelle' => 'required',
            ], [
                'libelle.required' => 'Veuillez entrer un libelle'
            ]);

            if ($valid->fails()) {
                return response()->json(['error' => $valid->errors()]);
            }
            
            $role = Role::where('ref', $ref)->firstOrFail();
            $role->update($valid->validated());
            

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => 'Role modifié avec succès',
                'objet' => $role
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

    //fonction de suppression
    public function delete($ref)
    {
        DB::beginTransaction();

        try{

            $role = Role::where('ref', $ref)->firstOrFail();
            $role->delete($ref);
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Role supprimé avec succès',
            ]);

        }catch(\Exception $e){

            return response()->json([
                'status_code' => 500,
                'Message' => 'Erreur interne de serveur ',
                'Error' => $e->getMessage()
            ]);
        }
    }
}
