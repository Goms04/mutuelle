<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref',
        'libelle',
        'description'
    ];


    public function user(){
        return $this->hasMany(User::class);
    }
}
