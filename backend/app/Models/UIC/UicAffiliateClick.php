<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicAffiliateClick extends Model
{
    protected $table = 'uic_affiliate_clicks';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(UicVisitorSession::class, 'session_id', 'session_id');
    }

    public function deal()
    {
        return $this->belongsTo(\App\Models\Deal::class, 'deal_id');
    }
}
