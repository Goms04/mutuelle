<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Historique extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'libelle',
        'montant',
        'user_id',
        'user_ref',
    ];



    public function user(){
        return $this->belongsTo(User::class);
    }
}
