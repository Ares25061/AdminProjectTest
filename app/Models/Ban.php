<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = [
        'user_id',
        'reason',
        'expiration',
    ];
}
