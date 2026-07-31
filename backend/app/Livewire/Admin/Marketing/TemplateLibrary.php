<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Communications\Template;

class TemplateLibrary extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = 'all';

    protected $queryString = ['search', 'category'];

    public function getCategoriesProperty()
    {
        return [
            'all' => 'All Templates',
            'ecommerce' => 'E-Commerce & Deals',
            'newsletters' => 'Newsletters',
            'transactional' => 'Transactional',
            'seasonal' => 'Seasonal & Holidays'
        ];
    }

    public function mount()
    {
        // Seed some defaults if none exist
        if (Template::count() === 0) {
            $this->seedDefaults();
        }
    }

    private function seedDefaults()
    {
        $defaults = [
            ['name' => 'Flash Sale Alert', 'category' => 'ecommerce', 'type' => 'Email', 'thumbnail_url' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?q=80&w=300&auto=format&fit=crop'],
            ['name' => 'Weekly Digest', 'category' => 'newsletters', 'type' => 'Email', 'thumbnail_url' => 'https://images.unsplash.com/photo-1503694978374-8a2fa686963a?q=80&w=300&auto=format&fit=crop'],
            ['name' => 'Black Friday', 'category' => 'seasonal', 'type' => 'Email', 'thumbnail_url' => 'https://images.unsplash.com/photo-1604323214371-2ed1eb24bb9a?q=80&w=300&auto=format&fit=crop'],
            ['name' => 'Welcome Email', 'category' => 'transactional', 'type' => 'Email', 'thumbnail_url' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=300&auto=format&fit=crop'],
        ];

        foreach ($defaults as $i => $data) {
            Template::create([
                'name' => $data['name'],
                'slug' => \Illuminate\Support\Str::slug($data['name']) . '-' . uniqid(),
                'category' => $data['category'],
                'type' => $data['type'],
                'thumbnail_url' => $data['thumbnail_url'],
                'is_system' => true,
                'html_content' => '<h1>' . $data['name'] . '</h1><p>Start editing this template.</p>',
                'tags' => [$data['category']],
            ]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function deleteTemplate($id)
    {
        Template::findOrFail($id)->delete();
        session()->flash('message', 'Template deleted successfully.');
    }

    public function getTemplatesProperty()
    {
        $query = Template::query();
        
        if ($this->category !== 'all') {
            $query->where('category', $this->category);
        }

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return $query->latest()->paginate(12);
    }

    public function render()
    {
        return view('livewire.admin.marketing.template-library')->layout('admin.layout');
    }
}
