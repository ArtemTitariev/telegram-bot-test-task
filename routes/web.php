<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


// Route::get('/set-webhook', function () {
//     $response = Http::post(config('telegram.api_url') . '/setWebhook', [
//             'url' => config('app.url'),
//     ]);

//     dd($response->json());
// });

Route::get('/', function () {
    return view('welcome');
});

Route::get('/upd', function () {
    $response = Http::get(config('telegram.api_url') . '/getUpdates'); 

    $updates = $response->json();

    if (! isset($updates['result']) || count($updates['result']) == 0) {
        return "Немає нових повідомлень для обробки.";
    }

    $lastUpdate = end($updates['result']);
    $from = $lastUpdate['message']['from'] ?? $lastUpdate['my_chat_member']['from'];
    $chat = $lastUpdate['message']['chat'] ?? $lastUpdate['my_chat_member']['chat'];
    
    $chatId = $chat['id'];
    $firstName = $from['first_name'];
    $lastName = $from['last_name'] ?? '';
    $username = $from['username'] ?? '';

    $greetingMessage = "Вітаю, $firstName $lastName (@" . $username . ")!";

    $sendResponse = Http::post(config('telegram.api_url') . '/sendMessage', [
        'chat_id' => $chatId,
        'text' => $greetingMessage,
    ]);

    $sendResult = $sendResponse->json();

    if (isset($sendResult['ok']) && $sendResult['ok']) {
        return "Повідомлення успішно відправлено!";
    } else {
        return "Помилка відправки повідомлення: " . ($sendResult['description'] ?? 'невідома помилка');
    }
});

Route::get('/user', function () {
    $response = Http::get(config('telegram.api_url') . '/getUpdates');

    dd($response->json());
});
