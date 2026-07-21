<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicEvent extends Model
{
    protected $table = 'uic_events';
    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(UicVisitorSession::class, 'session_id', 'session_id');
    }
}
