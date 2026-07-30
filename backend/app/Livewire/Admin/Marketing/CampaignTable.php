<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmailCampaign;
use App\Services\Marketing\CampaignAggregateService;
use App\Enums\CampaignState;
use App\Livewire\Traits\AuthorizesMarketing;
use Illuminate\Support\Facades\Log;

class CampaignTable extends Component
{
    use WithPagination;
    use AuthorizesMarketing;

    // Search & Filters
    public string $search = '';
    public array $filters = [
        'status' => '',
        'queue' => '',
        'provider' => '',
        'hasFailures' => false,
        'scheduledToday' => false,
    ];

    // Bulk Actions
    public array $selectedCampaigns = [];
    public bool $selectAll = false;

    // We reset pagination when searching/filtering
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCampaigns = $this->buildQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedCampaigns = [];
        }
    }

    protected function buildQuery()
    {
        return EmailCampaign::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('subject', 'like', '%' . $this->search . '%')
                      ->orWhere('id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], fn($q, $status) => $q->where('status', $status))
            ->when($this->filters['queue'], fn($q, $queue) => $q->where('queue', $queue))
            ->when($this->filters['provider'], fn($q, $provider) => $q->where('provider', $provider))
            ->when($this->filters['hasFailures'], fn($q) => $q->where('failed_count', '>', 0))
            ->when($this->filters['scheduledToday'], fn($q) => $q->whereDate('scheduled_at', today()))
            ->orderBy('created_at', 'desc');
    }

    // -- Bulk Actions -- //

    public function pauseSelected(CampaignAggregateService $service)
    {
        $this->authorizeMarketing('marketing.campaign.create'); // Or separate manage permission
        $this->performBulkTransition($service, CampaignState::PAUSED, 'Paused via bulk action');
    }

    public function resumeSelected(CampaignAggregateService $service)
    {
        $this->authorizeMarketing('marketing.campaign.create');
        // Resuming usually means moving back to Scheduled or Queued. For simplicity, setting it to Queued if it has recipients left.
        $this->performBulkTransition($service, CampaignState::QUEUED, 'Resumed via bulk action');
    }

    public function cancelSelected(CampaignAggregateService $service)
    {
        $this->authorizeMarketing('marketing.campaign.create');
        $this->performBulkTransition($service, CampaignState::CANCELLED, 'Cancelled via bulk action');
    }

    public function deleteSelected()
    {
        $this->authorizeMarketing('marketing.campaign.create'); // Requires delete permission realistically
        EmailCampaign::whereIn('id', $this->selectedCampaigns)->delete();
        $this->selectedCampaigns = [];
        $this->selectAll = false;
        // Optionally dispatch audit event
    }

    protected function performBulkTransition(CampaignAggregateService $service, CampaignState $newState, string $reason)
    {
        $campaigns = EmailCampaign::whereIn('id', $this->selectedCampaigns)->get();
        foreach ($campaigns as $campaign) {
            try {
                $service->transition($campaign, $newState, $reason);
            } catch (\Exception $e) {
                // Log and continue
                Log::warning("Bulk action failed for campaign {$campaign->id}: " . $e->getMessage());
            }
        }
        $this->selectedCampaigns = [];
        $this->selectAll = false;
    }

    public function render(CampaignAggregateService $aggregateService)
    {
        $this->authorizeMarketing('marketing.dashboard.view');

        $campaigns = $this->buildQuery()->paginate(10);

        return view('livewire.admin.marketing.campaign-table', [
            'campaigns' => $campaigns,
            'aggregateService' => $aggregateService,
        ]);
    }
}
