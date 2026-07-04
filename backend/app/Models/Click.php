<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referer'
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
