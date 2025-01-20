<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraitementEvenement extends Model
{
    use HasFactory;
    protected $fillable = [
        'ref',
        'evenement_id',
        'evenement_ref',
        'isfinished',
        'isvalidated',
        'message',
        'index',
        'traite_par',
        'date'
    ];

    public function evenement (){
        return $this->belongsTo(Evenement::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'traite_par');
    }
}
