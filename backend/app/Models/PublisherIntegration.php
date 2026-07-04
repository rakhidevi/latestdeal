<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'bot_token',
        'chat_id',
        'affiliate_tag'
    ];
}
