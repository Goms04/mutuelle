<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPret extends Model
{
    use HasFactory;
    
    protected $fillable  = [    
        'ref',
        'user_ref',
        'user_id',
        'motif',
        'montant',
        'nom',
        'prenom',
        'email',
    ];
}
