<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsTresorier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifiez si l'utilisateur est authentifié et s'il a le rôle d'administrateur
        if (Auth::check() && Auth::user()->role_id === 3) { // Supposons que 0 est le code pour administrateur
            return $next($request);
        }

        // Redirigez vers une page d'erreur ou la page d'accueil si l'utilisateur n'est pas administrateur
        return response()->json([
            'code' => 500,
            'status_message' => 'Vous ne disposez pas des droits pour effectuer cette action, Connectez vous simplement. Cordialement',
        ]);
    }
}
//Après ça on parle le déclarer dans Kernel.php puis definir les routes dans un group qu'on créera à ce niveau
