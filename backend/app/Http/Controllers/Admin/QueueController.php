<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\QueueService;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    protected $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function work()
    {
        $this->queueService->workQueue();
        return back()->with('success', 'Queue worker executed successfully.');
    }

    public function clear()
    {
        $this->queueService->clearFailedJobs();
        return back()->with('success', 'Failed jobs cleared successfully.');
    }
}
