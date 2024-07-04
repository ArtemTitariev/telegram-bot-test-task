<?php
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/set-webhook', function () {
//     $response = Http::post(config('telegram.api_url') . '/setWebhook', [
//             'url' => config('app.url') . 'telegram/webhook',
//             'allowed_updates' => ["callback_query", "message",],
//             // 'drop_pending_updates' => true,
//     ]);
   
//     Log::info('/set-webhook');
//     dd($response->json());
// });

// Route::get('/delete-webhook', function () {
//     $response = Http::post(config('telegram.api_url') . '/deleteWebhook');
//     Log::info('/delete-webhook');

//     dd($response->json());
// });

// Route::get('/get-webhook', function () {
//     $response = Http::post(config('telegram.api_url') . '/getWebhookInfo');
//     Log::info('/get-webhook');

//     dd($response->json());
// });



// Route::get('send/', function() {
//     $response = Http::post(config('telegram.api_url') . '/sendMessage', [
//         'chat_id' => 358360722,
//         'text' => "test",
//     ]);

//     dd($response);
    
// });

// Route::get('/upd', function () {
//     $response = Http::get(config('telegram.api_url') . '/getUpdates'); 

//     $updates = $response->json();

//     dd($updates);

//     if (! isset($updates['result']) || count($updates['result']) == 0) {
//         return "Немає нових повідомлень для обробки.";
//     }

//     $lastUpdate = end($updates['result']);
//     $from = $lastUpdate['message']['from'] ?? $lastUpdate['my_chat_member']['from'];
//     $chat = $lastUpdate['message']['chat'] ?? $lastUpdate['my_chat_member']['chat'];
    
//     $chatId = $chat['id'];
//     $firstName = $from['first_name'];
//     $lastName = $from['last_name'] ?? '';
//     $username = $from['username'] ?? '';

//     $greetingMessage = "Вітаю, $firstName $lastName (@" . $username . ")!";

//     $sendResponse = Http::post(config('telegram.api_url') . '/sendMessage', [
//         'chat_id' => $chatId,
//         'text' => $greetingMessage,
//     ]);

//     $sendResult = $sendResponse->json();

//     if (isset($sendResult['ok']) && $sendResult['ok']) {
//         return "Повідомлення успішно відправлено!";
//     } else {
//         return "Помилка відправки повідомлення: " . ($sendResult['description'] ?? 'невідома помилка');
//     }
// });
