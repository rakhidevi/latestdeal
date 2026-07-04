<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PublisherIntegration;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController
{
    /**
     * Handle incoming webhooks from Telegram.
     */
    public function handle(Request $request)
    {
        $update = $request->all();
        
        Log::info('Telegram Webhook Received', $update);

        if (isset($update['message']['text'])) {
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'];
            
            // Example flow: /start token
            if (str_starts_with($text, '/start')) {
                // The user could send a specific bot_token they generated from the dashboard
                // For now we just log it or auto-create an integration if we have a mapping
                Log::info("User with Chat ID {$chatId} started the bot.");
                
                // You could parse the token and link it to publisher_integrations
                // e.g. PublisherIntegration::where('bot_token', $token)->update(['chat_id' => $chatId]);
            }
        }
        
        return response()->json(['status' => 'success']);
    }
}
