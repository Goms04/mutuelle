<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remboursement extends Model
{
    use HasFactory;
    protected $fillable = [
        'ref',
        'date_remboursement',
        'montant',
        'pret_id',
        'pret_ref',
        'user_id',
        'ref_user',
        'email',
    ];


    public function pret(){
        return $this->belongsTo(Pret::class);
    }
}
