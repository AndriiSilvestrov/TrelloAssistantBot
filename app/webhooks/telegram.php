<?php

use App\Router;
use GuzzleHttp\Exception\ClientException;

require_once '../config/init.php';

$update = json_decode(file_get_contents('php://input'), true);

$router = new Router($telegramBot, $trelloAPI, $db);

try {
    switch (true) {
        case isset($update['message']):
            $router->handleMessage($update['message']);
            return;
        case isset($update['callback_query']):
            $router->handleCallbackQuery($update['callback_query']);
            return;
    }
} catch (PDOException $e) {
    $telegramBot->sendMessage($_ENV['TELEGRAM_GROUP_CHAT_ID'], 'Виникла помилка при взаємодії з базою даних.');
} catch (ClientException $e) {
    $telegramBot->sendMessage($_ENV['TELEGRAM_GROUP_CHAT_ID'], 'Виникла помилка при відправкі запиту до сторонього API.');
}