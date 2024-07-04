<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class TelegramBotService
{
    public function handleCommand($data)
    {
        $chat = $data['message']['chat'];
        
        if ($chat['type'] === 'private') {
            $this->sendMessageToUser(
                $chat['id'], 
                "Вибачте, але мене можна використовувати лише в групових чатах."
            );
            return;
        }
        
        $text = $data['message']['text'];


        switch ($text) {
            case '/start':
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
        
        $message = 'Вітаю, ' . $user->fullName . '!';

        return $this->sendMessageToUser($chatId, $message);
    }

    protected function handleDefaultMessage($chatId)
    {
        $message = 'Я не розумію вашої команди.';
        return $this->sendMessageToUser($chatId, $message);
    }

    protected function sendMessageToUser($chatId, $message)
    {
        $response = Http::post(config('telegram.api_url') . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
        ]);

        if ($response->failed()) {
            // Обробка 
            return false;
        }

        return true;
    }





}