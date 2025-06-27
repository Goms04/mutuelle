<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeEvenement extends Model
{
    use HasFactory;


    protected $fillable = [
        'ref',
        'lib_type',
        'montant',
        'deleted',
    ];


    public function evenement(){
        return $this->hasMany(Evenement::class);
    }


    public function userEvenement(){
        return $this->hasMany(UserEvenement::class);
    }

}
