<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicSearchHistory extends Model
{
    protected $table = 'uic_search_history';
    protected $guarded = [];

    protected $casts = [
        'clicked' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(UicVisitorSession::class, 'session_id', 'session_id');
    }
}
