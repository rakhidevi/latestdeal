<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealAiGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'generation_number',
        'generation_target',
        'model',
        'provider',
        'status',
        'content',
        'source_facts',
        'qa_result',
        'qa_feedback',
        'error',
        'content_confidence',
        'source_completeness',
        'qa_notes',
    ];

    protected $casts = [
        'content' => 'array',
        'source_facts' => 'array',
        'qa_notes' => 'array',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
