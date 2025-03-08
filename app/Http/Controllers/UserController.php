<?php

namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\Role;
use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    //Affichage de la page
    public function show()
    {
        $users = User::all();
        // Transformer la collection en un tableau de données souhaitées
        $userData = $users->map(function ($user) {
            return [
                'ref' => $user->ref,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'date_naissance' => $user->date_naissance,
                'email' => $user->email,
                'index' => $user->index,
                'sexe' => $user->sexe ? 'Masculin' : 'Féminin', // Affichage du sexe
                'role' => $user->role ? [
                    'ref' => $user->role->ref,
                    'libelle' => $user->role->libelle,
                ] : null, // Assurez-vous que le rôle existe
                'poste' => $user->poste ? [
                    'ref' => $user->poste->ref,
                    'libelle' => $user->poste->libelle,
                    'description' => $user->poste->description ?? null, // Assurez que la description existe
                ] : null,
                'subdivision' => $user->subdivision ? [
                    'ref' => $user->subdivision->ref,
                    'libelle' => $user->subdivision->libelle,
                    'description' => $user->subdivision->description ?? null, // Assurez que la description existe
                ] : null,
                'montant_a_cotiser' => $user->montant_a_cotiser,
                'solde_initial' => $user->solde_initial,
                'capital_brut' => $user->capital_brut,
                'capital_net' => $user->capital_net,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'OK',
            'objet' => $userData // Renvoie le tableau des utilisateurs
        ]);
    }


    public function index(Request $request, $ref)
    {
        DB::beginTransaction();
        try {
            $user = User::where('ref', $ref)->firstOrFail();
            $user->update([
                'index' => $request->input('index')
            ]);

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Index modifié avec succès !',
                'objet' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //Retrouver un seul utilisateur
    public function showobject($ref)
    {
        $user = User::where('ref', $ref)->firstOrFail();

        $userData = [
            'ref' => $user->ref,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'date_naissance' => $user->date_naissance,
            'email' => $user->email,
            'index' => $user->index,
            'sexe' => $user->sexe ? 'Masculin' : 'Féminin', // Affichage du sexe
            'role' => $user->role ? [
                'ref' => $user->role->ref,
                'libelle' => $user->role->libelle,
            ] : null, // Assurez-vous que le rôle existe
            'poste' => $user->poste ? [
                'ref' => $user->poste->ref,
                'libelle' => $user->poste->libelle,
                'description' => $user->poste->description ?? null, // Assurez que la description existe
            ] : null,
            'subdivision' => $user->subdivision ? [
                'ref' => $user->subdivision->ref,
                'libelle' => $user->subdivision->libelle,
                'description' => $user->subdivision->description ?? null, // Assurez que la description existe
            ] : null,
            'montant_a_cotiser' => $user->montant_a_cotiser,
            'capital_brut' => $user->capital_brut,
            'capital_net' => $user->capital_net,
        ];


        return response()->json([
            'code' => 200,
            'Message' => 'OK',
            'objet' => $userData
        ]);
    }

    //Fonction creation
    public function register(Request $request)
    {
        DB::beginTransaction();
        try {

            
            $role = Role::where('ref', $request->input('role_id'))->first();

            
            if (!$role) {
                return response()->json(['error' => 'Référence  de role non valides fournies'], 400);
            }

            $user = User::create([
                'ref' => Str::uuid(),
                'nom' => $request->input('nom'),
                'prenom' => $request->input('prenom'),
                'sexe' => $request->input('sexe'),
                'date_naissance' => $request->input('date_naissance'),
                'email' => $request->input('email'),
                'role_id' => $role->id,
                'solde_initial' => $request->solde_initial,
                'montant_a_cotiser' => $request->input('montant_a_cotiser'),
                'password' => Str::uuid(),
                'deleted' => false,
                'enabled' => false,
            ]);

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Mutualiste créé avec succès',
                'objet' => $user
            ]);
        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //fonction modification
    public function update(Request $request, $ref)
    {

        DB::beginTransaction();
        try {

            $users = $request->validate([
                'nom' => 'required',
                'prenom' => 'required',
                'sexe' => 'required',
                'date_naissance' => 'required',
                'email' => 'required',
                'role_id' => 'required',
                'montant_a_cotiser' => 'required',
            ]);

            $user = User::where('ref', $ref)->firstOrFail();
            
            $role = Role::where('ref', $request->input('role_id'))->first();

            $user->update([
                'nom' => $request->input('nom'),
                'prenom' => $request->input('prenom'),
                'sexe' => $request->input('sexe'),
                'date_naissance' => $request->input('date_naissance'),
                'email' => $request->input('email'),
                'role_id' => $role->id,
                'montant_a_cotiser' => $request->input('montant_a_cotiser'),
            ]);

            DB::commit();
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Mutualiste modifié avec succès',
                'objet' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur interne du serveur',
                'error' => $e->getMessage(),
            ]);
        }
    }

    //fonction de suppression
    public function delete($ref)
    {
        try {
            $user = User::where('ref', $ref)->firstOrFail();
            $user->update([
                'enabled' => false
            ]);
            return response()->json(['message' => 'Mutualiste supprimé avec succès'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la suppression du mutualiste', 'error' => $e->getMessage()], 500);
        }
    }


    public function _construct()
    {
        $this->middleware('auth::api', ['except' => ['login', 'register']]);
    }

    //Se connecter
    public function login(Request $request)
    {
        //$credentials = request(['email', 'password']); c'est ça qu'on devait utiliser au niveau de validator->validated() en bas

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Email ou mot de passe erroné',
            ]);
        }

        return $this->respondWithToken($token);
    }


    public function me()
    {
        return response()->json(auth()->user());
    }

    //Deconnexion
    public function logout()
    {
        try {
            // Récupérer le token actuel
            $token = JWTAuth::parseToken()->getToken();

            // Invalider le token
            JWTAuth::invalidate($token);

            return response()->json(['message' => 'Déconnecté avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la déconnexion'], 500);
        }
    }


    //réponse renvoyée lorqu'on veut se logger
    protected function respondWithToken($token)
    {
        $user = auth()->user();

        if ($user->enabled == true) {
            return response()->json([
                'token' => $token,
                'code' => 200,
                'message' => 'Connecté avec succès',
                'user' => [
                    'ref' => $user->ref,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'sexe' => $user->sexe ? 'Masculin' : 'Féminin', // Affichage du sexe
                    'role' => $user->role->libelle,
                ]
            ]);
        } else {
            return response()->json([
                'code' => 500,
                'message' => 'Veuillez vérifier votre adresse mail d\'abord'
            ]);
        }
    }


    public function userind() {
        $users = User::where('index', '>', 0)->get();
        
        return response()->json([
            'statut' => 200,
            'message' => 'Okay',
            'objet' => $users,
        ]);
    }


    public function reinitialiser(Request $request) {}
}
