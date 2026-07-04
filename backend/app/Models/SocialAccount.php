<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $fillable = [
        'platform',
        'account_name',
        'access_token',
        'target_id',
        'is_active',
    ];
}
