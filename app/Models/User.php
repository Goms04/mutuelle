<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, CanResetPassword, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'nom',
        'prenom',
        'sexe',
        'date_naissance',
        'email',
        'poste_id',
        'subdivision_id',
        'role_id',
        'montant_a_cotiser',
        'password',
        'deleted',
        'solde_initial',
        'index',
        'enabled',
    ];


    public function traitementEvenement(){
        return $this->hasMany(TraitementEvenement::class);
    }

    public function otp(){
        return $this->hasMany(Otpp::class);
    }

    public function poste(){
        return $this->belongsTo(Poste::class);
    }

    public function role(){
        return $this->belongsTo(Role::class);
    }


    public function subdivision(){
        return $this->belongsTo(Subdivision::class);
    }

    public function pret(){
        return $this->hasMany(Pret::class);
    }

    public function cotisation(){
        return $this->hasMany(Cotisation::class);
    }

    public function evenement(){
        return $this->hasMany(Evenement::class);
    }

    public function userEvenement(){
        return $this->hasMany(UserEvenement::class);
    }

    public function usercotisation(){
        return $this->hasMany(UserCotisation::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    /*****************************LES DEUX METHODEs IMPLEMENTEES POUR LE L'AUTHENTIFICATION JWT  *******************************************/


    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
