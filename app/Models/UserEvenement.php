<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvenement extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'ref_user_em',
        'ref_user_dest',
        'user_id',
        'ref_evenement',
        'evenement_id',
        'ref_typeEvenement',
        'typeEvenement_id',
        'montant',
        'nom_em',
        'prenom_em',
        'email_em',
        'description',
        'nom_dest',
        'prenom_dest',
        'email_dest',
        'date',
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }
    public function evenement(){
        return $this->belongsTo(Evenement::class);
    }
    public function typeEvenement(){
        return $this->belongsTo(TypeEvenement::class);
    }


}
