<?php

use App\Http\Controllers\CotisationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\OtppController;
use App\Http\Controllers\PosteController;
use App\Http\Controllers\PretController;
use App\Http\Controllers\RemboursementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SendOtp;
use App\Http\Controllers\SubdivisionController;
use App\Http\Controllers\TraitementEvenementController;
use App\Http\Controllers\TraitementPretController;
use App\Http\Controllers\TypeEvenementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserCotisationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */



//Ci-dessous les routes du peuple:authentification
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {

    Route::POST('login', UserController::class . '@login');
    Route::POST('logout', UserController::class . '@logout');
    Route::POST('refresh', UserController::class . '@refresh');
    Route::GET('me', UserController::class . '@me');

    Route::POST('motdepasseoublie', UserController::class . '@oublie');
    Route::POST('reinitialisermotdepasse', UserController::class . '@reinitialiser');

    Route::POST('sendotp', OtppController::class . '@sendOtp');
    Route::POST('sendotpp', OtppController::class . '@send');
    Route::POST('verifyotp', OtppController::class . '@verifyOtp');
    Route::POST('verifyEmail/{email}', OtppController::class . '@verifyEmail');
    Route::POST('resetpassword/', OtppController::class . '@reset');

});




//Ci-dessous les routes de l'admin
Route::group(['middleware' => ['api', 'admin']], function () {

    
    
    Route::POST('users/creer/', UserController::class . '@register');
    Route::PUT('/users/modifier/{ref}', UserController::class . '@update')->name('user.update');
    Route::POST('/users/supprimer/{ref}', UserController::class . '@delete')->name('user.delete');
    Route::GET('/users/{ref}', UserController::class . '@showobject')->name('user');
    Route::GET('/users/', UserController::class . '@show');
    // modifier un index
    Route::PUT('/users/modifier/index/{ref}', UserController::class . '@index');
    
    // Routes pour les rôles
    Route::POST('roles/creer/', RoleController::class . '@create');
    Route::POST('roles/supprimer/{ref}', RoleController::class . '@delete');
    Route::PUT('roles/modifier/{ref}', RoleController::class . '@update');
    Route::GET('roles/', RoleController::class . '@show');
    Route::GET('roles/{ref}', RoleController::class . '@showobject');
    
    // Routes pour les subdivisions
    Route::POST('subdivisions/creer/', SubdivisionController::class . '@create');
    Route::POST('subdivisions/supprimer/{ref}', SubdivisionController::class . '@delete');
    Route::PUT('subdivisions/modifier/{ref}', SubdivisionController::class . '@update');
    Route::GET('subdivisions/', SubdivisionController::class . '@show');
    Route::GET('subdivisions/{ref}', SubdivisionController::class . '@showobject');
    
    // Routes pour les postes
    Route::POST('postes/creer/', PosteController::class . '@create');
    Route::POST('postes/supprimer/{ref}', PosteController::class . '@delete');
    Route::PUT('postes/modifier/{ref}', PosteController::class . '@update');
    Route::GET('postes/', PosteController::class . '@show');
    Route::GET('postes/{ref}', PosteController::class . '@showobject');
    

    //Routes pour trésorier(middleware à créer après)
    //cotisation
    Route::POST('cotisations/creer', CotisationController::class . '@createco'); //Trésorier
    Route::POST('cotisations/creer/{ref}', CotisationController::class . '@cotisation'); //Trésorier
    Route::PUT('cotisations/modifier/{ref}', CotisationController::class . '@update');//Trésorier
    Route::GET('cotisations/', CotisationController::class . '@show'); //Trésorier
    Route::GET('cotisations/{ref}', CotisationController::class . '@showobject'); //Trésorier
    //ucotisation
    //Route::GET('ucotisations/', UserCotisationController::class . '@show');
    Route::GET('ucotisations/', UserCotisationController::class . '@showobject'); //Trésorier
    Route::GET('ucotisations/total', UserCotisationController::class . '@somme'); //tésorier
    //Type d'évènement
    Route::GET('type_evenements/', TypeEvenementController::class . '@show');//Trésorier
    Route::GET('type_evenements/{ref}', TypeEvenementController::class . '@showobject');//Trésorier
    Route::POST('type_evenements/creer', TypeEvenementController::class . '@create'); //Trésorier
    Route::PUT('type_evenements/modifier/{ref}', TypeEvenementController::class . '@update');//Trésorier
    Route::POST('type_evenements/supprimer/{ref}', TypeEvenementController::class . '@delete');//Trésorier
    
    
    //Evènement
    Route::GET('evenements/userev/{ref}', EvenementController::class . '@showuserev'); // Route spécifique avec paramètre****
    Route::GET('evenements/show', EvenementController::class . '@showe'); // Route spécifique (statique)
    Route::GET('evenements/count', EvenementController::class . '@countevenement');
    Route::GET('evenements/', EvenementController::class . '@show'); // Route statique pour la liste générale
    Route::GET('evenements/{ref}', EvenementController::class . '@showobject'); // Route dynamique avec paramètre
    Route::POST('evenements/creer', EvenementController::class . '@create'); 
    Route::PUT('evenements/modifier/{ref}', EvenementController::class . '@update');
    //Route::POST('evenements/supprimer/{ref}', EvenementController::class . '@delete');
    
    
    //Traitement
    Route::GET('evenements/traitements/{ref}', TraitementEvenementController::class . '@showlist');
    Route::POST('evenements/valider/{ref}', TraitementEvenementController::class . '@lancer'); 




    Route::GET('prets/all', PretController::class . '@showall'); //getall()
    Route::GET('prets/show', PretController::class . '@showme'); //show()
    Route::GET('prets/', PretController::class . '@show'); //getbyuser()
    Route::GET('prets/{ref}', PretController::class . '@showobject');
    Route::GET('prets/count', PretController::class . '@countpret');
    Route::POST('prets/creer', PretController::class . '@create'); 
    Route::PUT('prets/modifier/{ref}', PretController::class . '@update'); 
    Route::PUT('prets/modpret/{ref}', PretController::class . '@updatebyuser'); 
    
    Route::GET('prets/traitements/{ref}', TraitementPretController::class . '@showlist');
    Route::POST('prets/valider/{ref}', TraitementPretController::class . '@valider'); 
    
    
    Route::GET('remboursements/{ref}', RemboursementController::class . '@getone'); //get 1 remboursement
    Route::GET('remboursements/pret/{ref}', RemboursementController::class . '@getrp'); //get tous remboursement en fonction du pret
    Route::GET('remboursements/', RemboursementController::class . '@getall'); //get tous les remboursements
    Route::POST('/remboursements/lancer/{ref}', RemboursementController::class . '@rembourser'); //rembousements



    Route::GET('/dashboard/nbpret', DashboardController::class . '@npret');
    Route::GET('/dashboard/pretattente', DashboardController::class . '@npretatt');
    Route::GET('/dashboard/nbev', DashboardController::class . '@nev');
    Route::GET('/dashboard/evattente', DashboardController::class . '@nevatt');
    Route::GET('/dashboard/solde', DashboardController::class . '@solde');
    Route::GET('/dashboard/soldeindividuel', DashboardController::class . '@soldeind');
    
    
    Route::GET('/historique/', HistoriqueController::class . '@historique');
    


});




//Ci-dessous les routes du trésorier seul
Route::group(['middleware' => ['api', 'tresorier']], function () {

});




//Ci-dessous les routes du trésorier & admin
Route::group(['middleware' => ['api', 'admin', 'tresorier']], function () {

});




//Ci-dessous les routes de tout le monde
Route::group(['middleware' => ['api', 'auth']], function ($router) {

    //Déclaration d'évènement
    
    


});
