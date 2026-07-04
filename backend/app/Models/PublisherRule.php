<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherRule extends Model
{
    protected $fillable = [
        'user_id',
        'keyword',
        'min_discount',
        'category_id',
    ];
}
