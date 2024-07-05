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
        $data = $request->all();

        if (isset($data['message'])) {
            $this->telegramBotService->handleCommand($data);
        }

        return response()->json(['status' => 'ok']);
    }
}
