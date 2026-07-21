<?php

namespace App\Models\UIC;

use Illuminate\Database\Eloquent\Model;

class UicAiConversation extends Model
{
    protected $table = 'uic_ai_conversations';
    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(UicVisitorSession::class, 'session_id', 'session_id');
    }
}
