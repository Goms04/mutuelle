<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraitementPret extends Model
{
    use HasFactory;
    protected $fillable = [
        'ref',
        'pret_id',
        'pret_ref',
        'isfinished',
        'isvalidated',
        'message',
        'index',
        'traite_par',
        'date'
    ];

    public function pret (){
        return $this->belongsTo(Pret::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'traite_par');
    }
}
