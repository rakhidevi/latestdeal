<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Communications\Subscriber;
use App\Models\Communications\Tag;

class SubscribersModule extends Component
{
    use WithPagination;

    public $search = '';
    public $activeFilter = 'all'; // all, subscribed, suppressed, bounced, unengaged
    public $activeTag = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'activeFilter' => ['except' => 'all'],
        'activeTag' => ['except' => null],
    ];

    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
        $this->activeTag = null;
        $this->resetPage();
    }

    public function setTagFilter($tagSlug)
    {
        $this->activeTag = $tagSlug;
        $this->activeFilter = 'all';
        $this->resetPage();
    }

    public function getTagsProperty()
    {
        return Tag::orderBy('name')->get();
    }

    public function render()
    {
        $query = Subscriber::query()->with('tags');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('email', 'like', '%' . $this->search . '%')
                  ->orWhere('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->activeFilter !== 'all') {
            $query->where('status', $this->activeFilter);
        }

        if ($this->activeTag) {
            $query->whereHas('tags', function($q) {
                $q->where('slug', $this->activeTag);
            });
        }

        $subscribers = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.admin.marketing.subscribers-module', [
            'subscribers' => $subscribers
        ])->layout('admin.layout');
    }
}
