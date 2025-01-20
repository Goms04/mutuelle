<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'mois',
        'annee',
        'isdone',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function usercotisation()
    {
        return $this->hasMany(UserCotisation::class);
    }

}
