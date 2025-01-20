<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pret extends Model
{
    use HasFactory;


    protected $fillable = [
        'ref',
        'montant',
        'motif_pret', //
        'date_pret',
        'mode_remboursement', //boolean
        'montant_remboursement',
        'validated',
        'soldout', //boolean
        'prerequis', //boolean
        'isfinished', //boolean
        'duree',
        'marge_totale',
        'quotite_cessible',
        'montant_accorde',
        'duree_remboursement_accorde',
        'user_id',
        'index'
    ];


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function remboursement(){
        return $this->hasMany(Remboursement::class);
    }

    public function traitementPret(){
        return $this->hasMany(TraitementPret::class);
    }

}
