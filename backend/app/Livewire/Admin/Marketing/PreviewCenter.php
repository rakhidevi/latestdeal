<?php

namespace App\Livewire\Admin\Marketing;

use Livewire\Component;
use App\Models\Communications\Template;
use App\Models\Communications\Subscriber;
use App\Services\Communications\TemplateRenderer;
use Illuminate\Support\Facades\App;

class PreviewCenter extends Component
{
    public $selectedTemplateId = null;
    public $selectedSubscriberId = null;
    public $previewHtml = '';

    public function updatedSelectedTemplateId()
    {
        $this->generatePreview();
    }

    public function updatedSelectedSubscriberId()
    {
        $this->generatePreview();
    }

    public function generatePreview()
    {
        if (!$this->selectedTemplateId) {
            $this->previewHtml = '';
            return;
        }

        $template = Template::find($this->selectedTemplateId);
        if (!$template) {
            $this->previewHtml = 'Template not found.';
            return;
        }

        $subscriber = null;
        if ($this->selectedSubscriberId) {
            $subscriber = Subscriber::find($this->selectedSubscriberId);
        }

        // Generate dummy data if no subscriber is selected
        $data = [
            'user' => $subscriber ? [
                'first_name' => $subscriber->first_name ?? 'Valued',
                'last_name' => $subscriber->last_name ?? 'Customer',
                'email' => $subscriber->email
            ] : [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com'
            ],
            'deals' => [
                ['title' => 'Sample Deal 1', 'price' => '$19.99', 'url' => '#'],
                ['title' => 'Sample Deal 2', 'price' => '$29.99', 'url' => '#'],
            ],
            'brand' => [
                'name' => 'LatestDeal',
                'url' => 'https://latestdeal.in',
                'logo' => 'https://latestdeal.in/logo.png'
            ]
        ];

        try {
            $renderer = App::make(TemplateRenderer::class);
            $this->previewHtml = $renderer->render($template, $data);
        } catch (\Exception $e) {
            $this->previewHtml = '<div class="text-red-500 p-4 border border-red-200 bg-red-50 rounded">Error rendering template: ' . $e->getMessage() . '</div>';
        }
    }

    public function getTemplatesProperty()
    {
        return Template::orderBy('name')->get();
    }

    public function getSubscribersProperty()
    {
        return Subscriber::orderBy('first_name')->take(50)->get();
    }

    public function render()
    {
        return view('livewire.admin.marketing.preview-center')->layout('admin.layout');
    }
}
