<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicVisitorSession extends Model
{
    protected $table = 'uic_visitor_sessions';
    protected $primaryKey = 'session_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'bounce' => 'boolean',
        'is_bot' => 'boolean',
    ];

    public function visitor()
    {
        return $this->belongsTo(UicVisitor::class, 'visitor_uuid', 'visitor_uuid');
    }

    public function pageVisits()
    {
        return $this->hasMany(UicPageVisit::class, 'session_id', 'session_id');
    }

    public function events()
    {
        return $this->hasMany(UicEvent::class, 'session_id', 'session_id');
    }
}
