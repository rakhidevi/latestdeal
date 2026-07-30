<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'variables' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function logs()
    {
        return $this->hasMany(EmailLog::class, 'campaign_id');
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    // State Machine Transitions
    public function transitionTo($newStatus)
    {
        $allowedTransitions = [
            'Draft' => ['Queued', 'Cancelled'],
            'Queued' => ['Sending', 'Cancelled'],
            'Sending' => ['Paused', 'Completed', 'Cancelled'],
            'Paused' => ['Sending', 'Cancelled'],
            'Completed' => [],
            'Cancelled' => [],
        ];

        $currentStatus = $this->status;

        if (in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
            $this->status = $newStatus;
            
            if ($newStatus === 'Sending' && !$this->started_at) {
                $this->started_at = now();
            }
            if ($newStatus === 'Completed') {
                $this->completed_at = now();
            }
            
            $this->save();
            return true;
        }

        throw new \Exception("Invalid state transition from {$currentStatus} to {$newStatus}");
    }
}
