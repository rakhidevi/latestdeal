<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index()
    {
        $settings = $this->settingService->getSettings();
        return view('admin.settings', compact('settings'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'ollama_model' => 'nullable|string',
            'ollama_base_url' => 'nullable|url',
            'ai_auto_summarize' => 'nullable|string|in:enabled,disabled',
            'crawler_automated' => 'nullable|string|in:enabled,disabled',
            'crawler_manual' => 'nullable|string|in:enabled,disabled'
        ]);

        $this->settingService->saveSettings($request->all());

        return back()->with('success', 'AI Settings updated successfully.');
    }

    public function toggle(Request $request)
    {
        $request->validate(['key' => 'required|string', 'value' => 'required|string']);
        
        $this->settingService->toggleSetting($request->key, $request->value);
        
        return back()->with('success', 'Setting updated successfully!');
    }
}
