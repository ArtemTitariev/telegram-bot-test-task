<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrelloApiService
{
    /**
     * @var TelegramBotService
     */
    protected $telegram;

    const CARDS = [
        'IN_PROGRESS' => 'InProgress',
        'DONE' => 'Done',
    ];

    const ACTION = 'updateCard';

    public function __construct(TelegramBotService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function handleWebhook(array $data)
    {
        if (
            isset($data['action']['type']) &&
            $data['action']['type'] === self::ACTION
        ) {

            $listBefore = $data['action']['data']['listBefore']['name'] ?? null;
            $listAfter = $data['action']['data']['listAfter']['name'] ?? null;

            $telegramGroupId = config('telegram.group_id');
            
            if (
                $listBefore === self::CARDS['IN_PROGRESS'] &&
                $listAfter === self::CARDS['DONE']
            ) {
                
                $message = $this->makeMessage(
                    cardName: $data['action']['data']['card']['name'],
                    from: self::CARDS['IN_PROGRESS'],
                    to: self::CARDS['DONE']
                );

                $this->telegram->sendMessage($telegramGroupId, $message);
            } elseif (
                $listBefore === self::CARDS['DONE'] &&
                $listAfter === self::CARDS['IN_PROGRESS']
            ) {
                $message = $this->makeMessage(
                    cardName: $data['action']['data']['card']['name'],
                    from: self::CARDS['DONE'],
                    to: self::CARDS['IN_PROGRESS']
                );

                $this->telegram->sendMessage($telegramGroupId, $message);
            }
        }
    }

    protected function makeMessage($cardName, $from, $to) {
        return __('Картку ":card" переміщено з :from до :to.', [
            'card' => $cardName,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public static function fetchInProgressCards($boardId) {      
        $response = Http::get(config('trello.api_url') . "boards/{$boardId}/cards", [
            'key' => config('trello.key'),
            'token' => config('trello.token'),
        ]);

        if ($response->failed()) {
            Log::error('Помилка отримання карток з Trello: ' . $response->body());
            return [];
        }
        
        $cards = $response->json();

        $inProgressCards = array_filter($cards, function ($card) {
            $listId = $card['idList'];
            return static::isInProgressList($listId);
        });
        
        $result = array_map(function ($card) {
            return [
                'name' => $card['name'],
                'members' => $card['idMembers']
            ];
        }, $inProgressCards);

        return $result;
    }

    private static function isInProgressList($listId)
    {
        $response = Http::get(config('trello.api_url') . "lists/{$listId}", [
            'key' => config('trello.key'),
            'token' => config('trello.token'),
        ]);

        if ($response->failed()) {
            Log::error('Помилка отримання деталей списку з Trello: ' . $response->body());
            return false;
        }

        $list = $response->json();
        return $list['name'] === static::CARDS['IN_PROGRESS'];
    }
}
