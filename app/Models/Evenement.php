<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evenement extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ref',
        'user_id',
        'ref_user',
        'description',
        'typeEvenement_id',
        'ref_typeEvenement',
        'date',
        'validated',
        'isfinished',
        'index',
        'nom',
        'prenom',
        'email',
        'montant',
    ];

    public function traitementevenement (){
        return $this->hasMany(TraitementEvenement::class);
    }

    public function typeEvenement(){
        return $this->belongsTo(TypeEvenement::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function userEvenement(){
        return $this->hasMany(UserEvenement::class);
    }

}
