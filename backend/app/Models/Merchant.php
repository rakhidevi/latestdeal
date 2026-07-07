<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $fillable = ['name', 'domain', 'store_id', 'affiliate_param_key', 'status'];

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
