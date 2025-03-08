<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\SendOtpMail;
use App\Models\Otpp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Ichtrojan\Otp\Models\Otp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OtppController extends Controller
{
    //
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        //dd($user);
        $otpCode = Str::random(6);
        $expiresAt = Carbon::now()->addMinutes(10);

        Otpp::updateOrCreate(
            ['user_id' => $user->id],
            ['otp' => $otpCode, 'expires_at' => $expiresAt]
        );

        Mail::to($user->email)->send(new SendOtpMail($otpCode));

        return response()->json(['message' => 'OTP envoyé avec succès.'], 200);
    }


    public function send()
    {
        return response()->json(['message' => 'OTP envoyé avec succès.'], 200);
    }


    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        $otp = Otpp::where('user_id', $user->id)->where('otp', $request->otp)->first();

        if (!$otp) {
            return response()->json(['message' => 'OTP invalide.'], 400);
        }

        if (Carbon::now()->greaterThan($otp->expires_at)) {
            return response()->json(['message' => 'OTP expiré.'], 400);
        }

        // Vérification réussie
        $user->email_verified_at = now();
        $user->enabled = true;
        $user->save();

        // Supprimer l'OTP après vérification
        $otp->delete();

        return response()->json(['message' => 'Email vérifié avec succès.'], 200);
    }



    public function verifyEmail($email)
    {
        $em = User::where('email', $email)->firstOrFail();
        if ($em) {
            
            $otpCode = rand(10000, 99999);
            $expiresAt = Carbon::now()->addMinutes(10);

            Otpp::updateOrCreate(
                [
                    'user_id' => $em->id,
                    'otp' => $otpCode,
                    'email' => $em->email,
                    'expires_at' => $expiresAt
                ]
            );
        }
        // Effectuer une requête POST vers l'API Spring Boot
        $response = Http::post('http://192.168.1.15:9096/api/users', [
            'email' => $em->email,
            'otp' => $otpCode,
            /* 'expires_at' => $expiresAt->toISOString(), */
        ]);

        // Vérifier le code de statut de la réponse
        $statusCode = $response->status(); // Obtient le code de statut HTTP

        // Vérifier si la réponse est réussie
        if ($response->successful()) {
            // Récupérer les données JSON
            $data = $response->json(); // Convertit la réponse en tableau associatif

            // Accéder aux éléments spécifiques
            $message = $data['message'] ?? null; // Récupère le message (s'il existe)
            $object = $data['object'] ?? null; // Récupère l'objet (s'il existe)

            return [
                'code' => $statusCode,
                'message' => $message,
                'objet' => $object,
            ];
        } else {
            // Gestion des erreurs si la requête échoue
            return [
                'code' => $statusCode,
                'message' => 'Erreur lors de la communication avec l\'API',
                'objet' => null,
            ];
        }
    }


    public function reset(Request $request)
    {
        DB::beginTransaction();
        try {
            /*  $email = $request->input('email');
            $mot_de_passe = $request->input('mot_de_passe'); */

            $valid = Validator::make($request->all(), [
                'email' => 'email|required',
                'password' => 'required|string|confirmed', // Assurez-vous que le champ de confirmation est nommé "password_confirmation"
                'password_confirmation' => 'required'
            ], [
                // Messages d'erreur personnalisés (facultatif)
                'email.required' => 'L\'adresse e-mail est requise.',
                'password.required' => 'Le mot de passe est requis.',
                'password.confirmed' => 'Les mots de passe ne correspondent pas.',
                'password_confirmation.required' => 'La confirmation est requise.'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'code' => 400,
                    'erreur' => $valid->errors()
                ]);
            }

            // Trouver l'utilisateur par email
            $user = User::where('email', $request->email)->first();
            $otpp = Otpp::where('email', $user->email)->first();
            $otp = $otpp->otp;

            if ($user) {

                $response = Http::get('http://192.168.1.15:9096/api/users?token=' . $otp);

                // Vérifier le code de statut de la réponse
                $statusCode = $response->status(); // Obtient le code de statut HTTP
                // Vérifier si la réponse est réussie
                if ($response->successful()) {
                    // Récupérer les données JSON
                    $data = $response->json(); // Convertit la réponse en tableau associatif

                    // Accéder aux éléments spécifiques
                    $message = $data['message'] ?? null; // Récupère le message (s'il existe)
                    $object = $data['object'] ?? null; // Récupère l'objet (s'il existe)

                    if($object == false){

                        $user->update([
                            'password' => $request->input('password'),
                            'enabled' => true
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'mot de passe modifié avec succès'
            ]);  

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => 'Erreur interne de serveur'
            ]);
        }
    }
}
