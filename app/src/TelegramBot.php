<?php

namespace App;

use GuzzleHttp\Client;

class TelegramBot
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => "https://api.telegram.org/bot{$_ENV['TELEGRAM_API_TOKEN']}/"]);
    }

    public function sendMessage(int $chatId, string $message): bool
    {
        $response = $this->client->post('sendMessage', [
            'json' => [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]
        ]);

        return json_decode($response->getBody(), true)['ok'];
    }

    public function sendInlineKeyboard(int $chatId, string $message, array $keyboard): bool
    {
        $response = $this->client->post('sendMessage', [
            'json' => [
                'chat_id' => $chatId,
                'text' => $message,
                'reply_markup' => [
                    'inline_keyboard' => [$keyboard]
                ],
            ]
        ]);

        return json_decode($response->getBody(), true)['ok'];
    }

    public function sendAnswerCallbackQuery(int $callbackQueryId, string $message): bool
    {
        $response = $this->client->post('answerCallbackQuery', [
            'json' => [
                'callback_query_id' => $callbackQueryId,
                'text' => $message
            ]
        ]);

        return json_decode($response->getBody(), true)['ok'];
    }

    public function getUserFullNameIfMember(int $userId): string|false
    {
        $response = $this->client->post('getChatMember', [
            'json' => [
                'chat_id' => $_ENV['TELEGRAM_GROUP_CHAT_ID'],
                'user_id' => $userId,
            ]
        ]);
    
        $response = json_decode($response->getBody(), true);
        if (!$response['ok'] || $response['result']['status'] === 'left') {
            return false;
        } 

        $fullName = array();
        $fullName[] = $response['result']['user']['first_name'];
        $fullName[] = $response['result']['user']['last_name'];

        $fullName = array_filter($fullName);

        return $fullName ? implode(' ', $fullName) 
            : ($response['result']['user']['username'] ?? 'НЕВІДОМИЙ');
    }
}