<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicPageVisit extends Model
{
    protected $table = 'uic_page_visits';
    protected $guarded = [];

    protected $casts = [
        'time_entered' => 'datetime',
        'time_left' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(UicVisitorSession::class, 'session_id', 'session_id');
    }
}
