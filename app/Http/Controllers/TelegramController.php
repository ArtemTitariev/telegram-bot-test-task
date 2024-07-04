<?php

namespace App\Http\Controllers;

use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * @var App\Services\TelegramBotService
     */
    protected $telegramBotService;

    public function __construct(TelegramBotService $telegramBotService)
    {
        $this->telegramBotService = $telegramBotService;
    }

    public function handleWebhook(Request $request)
    {   
        Log::info('-----Received webhook request from Telegram:------');

        $data = $request->all();
        // Log::info($data);
        // message->chat->type= private || group

        if (isset($data['message'])) {
            // $chatId = $data['message']['chat']['id'];
            // $text = $data['message']['text'];

            // Обробка команди через сервіс
            $this->telegramBotService->handleCommand($data);
        }

        return response()->json(['status' => 'ok']);
    }
}
