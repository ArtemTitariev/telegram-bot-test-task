<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    const CHAT_TYPES = [
        'PRIVATE' => 'private',
    ];

    const COMMANDS = [
        'START' => '/start',
    ];

    public function handleCommand($data)
    {
        $chat = $data['message']['chat'];
        
        if ($chat['type'] === self::CHAT_TYPES["PRIVATE"]) {
            $this->sendMessage(
                $chat['id'],
                __('Вибачте, але мене можна використовувати лише в групових чатах.')
            );
            return;
        }

        $text = $data['message']['text'];
        switch ($text) {
            case self::COMMANDS["START"]:
                $from = $data['message']['from'];
                return $this->handleStartCommand($chat['id'], $from);

            default:
                return $this->handleDefaultMessage($chat['id']);
        }
    }

    protected function handleStartCommand($chatId, $from)
    {
        $user = User::firstOrCreate(
            ['telegram_id' => $from['id']],
            [
                'telegram_id' => $from['id'],
                'first_name' => $from['first_name'],
                'last_name' => $from['last_name'] ?? null,
                'username' => $from['username'] ?? null,
            ]
        );

        $message = sprintf(__('Вітаю, %s!'), $user->fullName);

        return $this->sendMessage($chatId, $message);
    }

    protected function handleDefaultMessage($chatId)
    {
        $message = __('Я не розумію вашої команди.');
        return $this->sendMessage($chatId, $message);
    }

    public function sendMessage($chatId, $message)
    {
        $response = Http::post(config('telegram.api_url') . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
        ]);

        if ($response->failed()) {
            Log::error('Помилка відправки повідомлення в Telegram: ' .
                $response->body());

            return false;
        }

        return true;
    }
}
