<?php

use GuzzleHttp\Exception\ClientException;

require_once '../config/init.php';

$update = json_decode(file_get_contents('php://input'), true);

$translationKey = $update['action']['display']['translationKey'] ?? null;

try {
    switch ($translationKey) {
        case 'action_move_card_from_list_to_list':
            $cardName =  $update['action']['data']['card']['name'];
            $listBefore = $update['action']['data']['listBefore']['name'];
            $listAfter = $update['action']['data']['listAfter']['name'];
            
            $message = "Картка \"{$cardName}\" переміщена з колонки \"{$listBefore}\" в колонку \"{$listAfter}\".";
            
            $telegramBot->sendMessage($_ENV['TELEGRAM_GROUP_CHAT_ID'], $message);
            break;
    }
} catch (ClientException $e) {
    $telegramBot->sendMessage($_ENV['TELEGRAM_GROUP_CHAT_ID'], 'Виникла помилка при відправкі запиту до сторонього API.');
}