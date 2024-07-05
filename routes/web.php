<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TrelloController;

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);

Route::post('/trello/webhook', [TrelloController::class, 'handleWebhook']);


Route::get('/', function () {
    return view('welcome');
});

Route::get('user/', function() {
    $username = 'artemtitariev1';
    
    $response = Http::get(config('trello.api_url'). "members/{$username}", [
        'fields' => 'id,username,idBoards',
        'key' => config('trello.key'),
        'token' => config('trello.token'),
    ]);

    dd($response->json());
});

// Route::get('/set-telegram-webhook', function () {
//     $response = Http::post(config('telegram.api_url') . '/setWebhook', [
//             'url' => config('app.url') . 'telegram/webhook',
//             'allowed_updates' => ["callback_query", "message",],
//             // 'drop_pending_updates' => true,
//     ]);
//     dd($response->json());
// });

// Route::get('/set-trello-webhook', function () {
//     $response = Http::post("https://api.trello.com/1/webhooks/", [
//         'description' => "Test task",
//         'callbackURL' => config('app.url') . 'trello/webhook',
//         'idModel' => config('trello.board_id'),
//         'key' => config('trello.key'),
//         'token' => config('trello.token'),
//       ], 
//     );
//     dd($response->json());
// });
