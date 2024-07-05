<?php

return [
    'bot_key' => env('TELEGRAM_BOT_KEY'),

    'api_url' => env('TELEGRAM_API_BOT_URL', 'https://api.telegram.org/bot') .
        env('TELEGRAM_BOT_KEY'),

    'group_id' => env('TELEGRAM_GROUP_ID'),
];
