<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;

class AssetVariant extends Model
{
    protected $guarded = [];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
