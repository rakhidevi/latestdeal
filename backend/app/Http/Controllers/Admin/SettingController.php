<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        $this->settingService->saveSettings($request->all());

        return back()->with('success', 'Settings updated successfully.');
    }

    public function toggle(Request $request)
    {
        $request->validate(['key' => 'required|string', 'value' => 'required|string']);
        
        $this->settingService->toggleSetting($request->key, $request->value);
        
        return back()->with('success', 'Setting updated successfully!');
    }

    public function testSmtp(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);
        $recipient = $request->test_email;

        try {
            Mail::raw("Hello! This is a test email from LatestDeal Admin Panel to verify your SMTP configuration is working perfectly.", function ($message) use ($recipient) {
                $message->to($recipient)
                        ->subject("✅ LatestDeal SMTP Connection Test Successful");
            });

            return back()->with('success', "SMTP test message sent successfully to {$recipient}!");
        } catch (\Throwable $e) {
            return back()->with('error', "SMTP Connection Failed: " . $e->getMessage());
        }
    }
}
