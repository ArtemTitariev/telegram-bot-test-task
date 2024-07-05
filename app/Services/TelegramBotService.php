<?php

namespace App\Services;

use App\Models\User;
use App\Models\TrelloUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    const CHAT_TYPES = [
        'PRIVATE' => 'private',
    ];

    const COMMANDS = [
        'START' => '/start',
        'LINK_TRELLO' => '/linkTrello',
        'REPORT' => '/report',
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
        $from = $data['message']['from'];
        $chatId = $chat['id'];

        switch ($text) {
            case self::COMMANDS["START"]:
                return $this->handleStartCommand($chatId, $from);

            case (strpos($text, self::COMMANDS['LINK_TRELLO']) === 0):
                return $this->handleLinkTrelloCommand($chatId, $from, $text);

            case self::COMMANDS['REPORT']:
                $user = User::where('telegram_id', $from['id'])->first();
                if ($user->is_pm) {
                    return $this->handleReportCommand($chatId);
                } else {
                    return $this->sendMessage(
                        $chatId,
                        __('Тільки PM може генерувати звіт.')
                    );
                }
            default:
                return $this->handleDefaultMessage($chatId);
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

    protected function handleLinkTrelloCommand($chatId, $from, $text)
    {
        $parts = explode(' ', $text);
        $username = isset($parts[1]) ? $parts[1] : null;

        if (!$username) {
            $this->sendMessage(
                $chatId,
                __("Не вказано username для зв'язку з Trello.")
            );

            return false;
        }

        $this->linkTrello($chatId, $from['id'], $username);
    }

    protected function handleReportCommand($chatId)
    {
        $boardId = config('trello.board_id');
        $inProgressCards = TrelloApiService::fetchInProgressCards($boardId);

        // Log::alert($inProgressCards);
        // return;

        $members = TrelloUser::with('user')->get();

        $report = [];
        // Обхід користувачів
        foreach ($members as $trelloUser) {
            $fullname = $trelloUser->user->fullName;
            $report[$fullname] = [];
            // Обхід карток
            foreach ($inProgressCards as $card) {
                // Якщо в учасниках
                if (in_array($trelloUser->trello_id, $card['members'])) {
                    $report[$fullname][] = $card['name'];
                }
            }
        }

        // Формування звіту
        $reportString = "Звіт по учасникам про активні завдання (в InProgress):\n";
        foreach ($report as $username => $cards) {
            $reportString .= "Користувач: " . $username . "\n";
            foreach ($cards as $cardName) {
                $reportString .= "  - Картка: " . $cardName . "\n";
            }
        }

        $this->sendMessage($chatId, $reportString);
    }

    public function linkTrello($chatId, $telegramUserId, $trelloUsername)
    {
        $memberData = $this->getTrelloMemberData($trelloUsername);

        if ($memberData) {
            if ($this->isMemberOfBoard($memberData['idBoards'], config('trello.board_id'))) {
                $trelloUser = TrelloUser::firstOrCreate(
                    ['trello_id' => $memberData['id']],
                    [
                        'trello_id' => $memberData['id'],
                        'username' => $memberData['username'],
                    ]
                );

                $user = User::where('telegram_id', $telegramUserId)->first();

                if ($user) {
                    $this->linkUserToTrello($user, $trelloUser, $chatId);
                } else {
                    $this->sendMessage($chatId, __("Користувач Telegram не знайдений!"));
                }
            } else {
                $this->sendMessage($chatId, sprintf(__("Учасника дошки зі username %s не знайдено."), $memberData['username']));
            }
        } else {
            $this->sendMessage($chatId, __("Користувача Trello зі вказаним username не знайдено"));
        }
    }

    private function getTrelloMemberData($trelloUsername)
    {
        $response = Http::get(config('trello.api_url') . "members/{$trelloUsername}", [
            'fields' => 'id,username,idBoards',
            'key' => config('trello.key'),
            'token' => config('trello.token'),
        ]);

        return $response->successful() ? $response->json() : null;
    }

    private function isMemberOfBoard(array $idBoards, $boardId)
    {
        return in_array($boardId, $idBoards);
    }

    private function linkUserToTrello(User $user, TrelloUser $trelloUser, $chatId)
    {
        if (!$user->trello_user_id) {
            $user->trelloUser()->associate($trelloUser);
            $user->save();
            $this->sendMessage($chatId, sprintf(__("Обліковий запис Trello %s успішно прив'язаний!"), $trelloUser->username));
        } else {
            $this->sendMessage($chatId, sprintf(__("Обліковий запис Trello %s вже прив'язаний!"), $trelloUser->username));
        }

        $this->checkPM($user, $trelloUser);
    }

    private function checkPM(User $user, TrelloUser $trelloUser)
    {
        $boardId = config('trello.board_id');

        $response = Http::get(config('trello.api_url') . "boards/{$boardId}/memberships", [
            'key' => config('trello.key'),
            'token' => config('trello.token'),
            'filter' => 'admin',
        ]);

        if ($response->successful()) {
            $members = $response->json();
            foreach ($members as $member) {
                if ($member['idMember'] === $trelloUser->trello_id && $member['memberType'] === 'admin') {
                    $user->is_pm = true;
                    $user->save();

                    return true;
                }
            }

            return false;
        }
    }


    protected function handleDefaultMessage($chatId)
    {
        $message = __('Я не розумію вашої команди.');
        return $this->sendMessage($chatId, $message);
    }

    public function sendMessage($chatId, $message, $reply = array())
    {
        $response = Http::post(config('telegram.api_url') . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
            // 'reply_markup' => json_encode($reply),
        ]);

        if ($response->failed()) {
            Log::error('Помилка відправки повідомлення в Telegram: ' .
                $response->body());

            return false;
        }

        return true;
    }
}
