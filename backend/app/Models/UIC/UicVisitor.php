<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicVisitor extends Model
{
    protected $table = 'uic_visitors';
    protected $primaryKey = 'visitor_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
    ];

    public function sessions()
    {
        return $this->hasMany(UicVisitorSession::class, 'visitor_uuid', 'visitor_uuid');
    }
}
