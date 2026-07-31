<?php

namespace App\Models\Communications;

use Illuminate\Database\Eloquent\Model;

class EmailTheme extends Model
{
    protected $guarded = [];

    protected $casts = [
        'manifest' => 'array',
        'is_default' => 'boolean',
    ];
}
