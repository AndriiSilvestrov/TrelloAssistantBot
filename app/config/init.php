<?php

use App\Database;
use App\TelegramBot;
use App\TrelloAPI;

define('APP_DIR', dirname(__DIR__));

require_once APP_DIR . '/vendor/autoload.php';

if (!file_exists(APP_DIR . '/data/database.sqlite')) {
    $file = fopen(APP_DIR . '/data/database.sqlite', 'w');
    fclose($file);
}

$db = new Database(APP_DIR . '/data/database.sqlite');
$telegramBot = new TelegramBot();
$trelloAPI = new TrelloAPI();