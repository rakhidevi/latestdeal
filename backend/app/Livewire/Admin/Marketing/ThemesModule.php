<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Models\Communications\EmailTheme;
use Illuminate\Support\Str;

class ThemesModule extends Component
{
    public $isEditing = false;
    public $themeId = null;
    
    public $name = '';
    public $manifest = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'manifest' => 'required|json'
    ];

    public function mount()
    {
        // Seed default theme if none exists
        if (EmailTheme::count() === 0) {
            EmailTheme::create([
                'name' => 'LatestDeal Default',
                'slug' => 'latestdeal-default',
                'is_default' => true,
                'manifest' => $this->getDefaultManifest()
            ]);
        }
    }

    public function createTheme()
    {
        $this->resetForm();
        $this->manifest = json_encode($this->getDefaultManifest(), JSON_PRETTY_PRINT);
        $this->isEditing = true;
    }

    public function editTheme($id)
    {
        $theme = EmailTheme::findOrFail($id);
        $this->themeId = $theme->id;
        $this->name = $theme->name;
        $this->manifest = json_encode($theme->manifest, JSON_PRETTY_PRINT);
        $this->isEditing = true;
    }

    public function saveTheme()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'manifest' => json_decode($this->manifest, true),
        ];

        if ($this->themeId) {
            EmailTheme::findOrFail($this->themeId)->update($data);
        } else {
            EmailTheme::create($data);
        }

        $this->isEditing = false;
        session()->flash('message', 'Theme saved successfully.');
    }

    public function deleteTheme($id)
    {
        $theme = EmailTheme::findOrFail($id);
        if ($theme->is_default) {
            session()->flash('error', 'Cannot delete the default theme.');
            return;
        }
        $theme->delete();
        session()->flash('message', 'Theme deleted successfully.');
    }
    
    public function makeDefault($id)
    {
        EmailTheme::where('is_default', true)->update(['is_default' => false]);
        EmailTheme::findOrFail($id)->update(['is_default' => true]);
        session()->flash('message', 'Default theme updated.');
    }

    public function cancel()
    {
        $this->isEditing = false;
    }

    private function resetForm()
    {
        $this->themeId = null;
        $this->name = '';
        $this->manifest = '';
        $this->resetErrorBag();
    }

    private function getDefaultManifest()
    {
        return [
            'brand' => [
                'logo' => url('images/logo.png'),
                'favicon' => url('favicon.ico'),
            ],
            'colors' => [
                'primary' => '#ff5722',
                'secondary' => '#3f51b5',
                'background' => '#f5f7fa',
                'surface' => '#ffffff',
                'text' => '#1e293b',
                'muted' => '#64748b'
            ],
            'typography' => [
                'font_family' => 'Inter, -apple-system, sans-serif',
                'h1_size' => '24px',
                'body_size' => '16px',
            ],
            'spacing' => [
                'container_width' => '600px',
                'padding' => '24px',
            ],
            'components' => [
                'button' => [
                    'border_radius' => '6px',
                    'padding' => '12px 24px',
                ],
                'card' => [
                    'border_radius' => '8px',
                    'border' => '1px solid #e2e8f0',
                ]
            ]
        ];
    }

    public function render()
    {
        return view('livewire.admin.marketing.themes-module', [
            'themes' => EmailTheme::latest()->get()
        ])->layout('admin.layout');
    }
}
