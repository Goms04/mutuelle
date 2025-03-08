<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCotisation extends Model
{
    use HasFactory;


    protected $fillable = [
        'ref',
        'ref_user',
        'ref_cotisation',
        'mois',
        'annee',
        'email',
        'nom',
        'prenom',
        /* 'capital_brut',
        'capital_net', */
        'montant_cotise',
        'user_id',
        'cotisation_id',
    ];



    public function user(){
        return $this->belongsTo(User::class);
    }

    public function cotisation(){
        return $this->belongsTo(Cotisation::class);
    }
}
