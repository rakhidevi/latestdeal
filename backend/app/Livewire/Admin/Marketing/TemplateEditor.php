<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Models\Communications\Template;
use App\Models\Communications\EmailTheme;
use App\Services\Communications\TemplateRenderer;
use Illuminate\Support\Str;

class TemplateEditor extends Component
{
    public $templateId = null;
    public $name = '';
    public $description = '';
    public $category = 'ecommerce';
    public $type = 'Email';
    public $html_content = '';
    
    // Editor UI state
    public $activeTab = 'editor'; // editor, preview
    public $previewHtml = '';

    public function mount($id = null)
    {
        if ($id) {
            $template = Template::findOrFail($id);
            $this->templateId = $template->id;
            $this->name = $template->name;
            $this->description = $template->description;
            $this->category = $template->category;
            $this->type = $template->type;
            $this->html_content = $template->html_content;
        } else {
            $this->html_content = "<!-- Add your HTML or use the Visual Builder here -->\n<h1>Hello World</h1>\n<p>Start editing your template.</p>";
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'type' => 'required|string',
            'html_content' => 'required|string'
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'type' => $this->type,
            'html_content' => $this->html_content,
        ];

        if ($this->templateId) {
            Template::findOrFail($this->templateId)->update($data);
            session()->flash('message', 'Template updated successfully.');
        } else {
            $data['slug'] = Str::slug($this->name) . '-' . uniqid();
            $template = Template::create($data);
            return redirect()->route('admin.marketing.templates.edit', $template->id);
        }
    }

    public function generatePreview(TemplateRenderer $renderer)
    {
        $theme = EmailTheme::where('is_default', true)->first();
        if (!$theme) {
            $theme = new EmailTheme(['name' => 'Default Theme']);
        }
        
        $this->previewHtml = $renderer->render($theme, [
            ['type' => 'RawHtmlBlock', 'settings' => ['html' => $this->html_content]]
        ]);
        
        $this->activeTab = 'preview';
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.admin.marketing.template-editor')->layout('admin.layout');
    }
}
