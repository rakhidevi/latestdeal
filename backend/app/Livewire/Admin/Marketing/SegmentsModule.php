<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Communications\Segment;

class SegmentsModule extends Component
{
    use WithPagination;

    public $search = '';
    public $isEditing = false;
    public $editingSegment = null;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function createSegment()
    {
        $this->editingSegment = new Segment([
            'type' => 'dynamic',
            'rules' => []
        ]);
        $this->isEditing = true;
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->editingSegment = null;
    }

    public function render()
    {
        $segments = Segment::query();

        if (!empty($this->search)) {
            $segments->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.admin.marketing.segments-module', [
            'segments' => $segments->orderBy('name')->paginate(20)
        ])->layout('admin.layout');
    }
}
