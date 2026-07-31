<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $guarded = [];

    protected $casts = [
        'blocks' => 'array',
        'tags' => 'array',
        'is_system' => 'boolean',
    ];
}
