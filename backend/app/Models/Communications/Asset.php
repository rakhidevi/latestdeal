<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $guarded = [];

    public function variants()
    {
        return $this->hasMany(AssetVariant::class);
    }
}
