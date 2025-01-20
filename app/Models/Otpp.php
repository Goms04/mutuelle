<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otpp extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'user_id',
        'otp',
        'email',
        'expires_at'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
