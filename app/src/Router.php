<?php

namespace App;

class Router
{
    private TelegramBot $telegramBot;
    private TrelloAPI $trelloAPI;
    private Database $db;

    public function __construct(TelegramBot $telegramBot, TrelloAPI $trelloAPI, Database $db)
    {
        $this->telegramBot = $telegramBot;
        $this->trelloAPI = $trelloAPI;
        $this->db = $db;
    }

    public function handleMessage(array $message): bool
    {
        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;

        if (!isset($chatId, $userId)) {
            return false;
        }

        $text = trim($message['text']);

        $this->startSession($chatId, $userId);

        if (strpos($text, '/') === 0) {
            return $this->handleMessageAsCommand($message);
        }

        if (isset($message['new_chat_participant'])) {
            return $this->handleMessageAddUser($message);
        }
        
        if (isset($_SESSION['wait_for_email'])) {
            if (filter_var($text, FILTER_VALIDATE_EMAIL)) {
                $this->telegramBot->sendMessage($chatId, 'Ваш email зареєстровано!');
                $this->db->addEmail($userId, $text);
                unset($_SESSION['wait_for_email']);
                $this->telegramBot->sendInlineKeyboard($chatId, 'Бажаєте відправити запрошення для підключення до дошки Trello на Ваш email?', [['text' => 'Відправити запрошення', 'callback_data' => 'join_trello']]);
            } else {
                $this->telegramBot->sendMessage($chatId, 'Ви ввели некоректний email, спробуйте ще раз.');
            }
        }

        return true;
    }

    public function handleMessageAsCommand(array $message): bool
    {
        $chatId = $message['chat']['id'] ?? null;
        $userId = $message['from']['id'] ?? null;

        if (!isset($chatId, $userId)) {
            return false;
        }

        $userName = $message['from']['first_name'] ?: $message['from']['username'];
        $text = explode('@', trim($message['text']))[0];

        switch ($text) {
            case '/start':
                $message = "Вітаю {$userName}!";
                
                if (!$this->db->userExists($userId)) {
                    $this->db->addUser($userId);
                }

                if (!$this->db->userHasEmail($userId)) {
                    $message .= "\nНадішліть будьласка ваш email для продовження реєстрації.";
                    $_SESSION['wait_for_email'] = true;
                }
        
                $this->telegramBot->sendMessage($chatId, $message);
                break;
            case '/join':
                if ($this->db->userHasEmail($userId)) {
                    $message = $this->sendInvitationToTrello($userId);
                } else {
                    $message = "Надішліть будьласка ваш email для продовження реєстрації.";
                    $_SESSION['wait_for_email'] = true;
                }

                $this->telegramBot->sendMessage($chatId, $message);
                break;
            case '/report':
                $report = array();

                foreach($this->db->getAllUsers() as $user) {
                    $userFullName = $this->telegramBot->getUserFullNameIfMember($user['telegram_id']);
                    if (!$userFullName) {
                        continue;
                    }

                    $report[$user['trello_id']] = [
                        'full_name' => $userFullName,
                        'task_num' => 0,
                    ];
                }

                foreach($this->trelloAPI->getCardsOfList($_ENV['TRELLO_IN_PROGRESS_LIST_ID']) as $card) {
                    if (empty($card['idMembers'])) {
                        continue;
                    }

                    foreach ($card['idMembers'] as $memberId) {
                        if (isset($report[$memberId])) {
                            $report[$memberId]['task_num']++;
                        }
                    }
                }

                $this->telegramBot->sendMessage($chatId, "<b>Звіт:</b>\n" . implode("\n", array_map(fn($userData) => $userData['full_name'] . ' - ' . $userData['task_num'], $report)));
                break;
            case '/init':
                if ($message['from']['username'] !== $_ENV['TELEGRAM_GROUP_ADMIN']) {
                    break;
                }
                $this->db->createTable();
                $this->telegramBot->sendMessage($chatId, 'Додаткові модулі розгорнуто!');
                break;
        }

        return true;
    }

    public function handleMessageAddUser(array $message): bool
    {
        $userId = $message['new_chat_participant']['id'];

        if (empty($userId) || $message['new_chat_participant']['is_bot']) {
            return false;
        }

        $this->db->addUser($userId);
        return true;
    }

    public function handleCallbackQuery(array $callbackQuery): bool
    {
        $callbackQueryId = $callbackQuery['id'] ?? null;
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $userId = $callbackQuery['from']['id'] ?? null;
        $callbackData = $callbackQuery['data'] ?? null;

        if (!isset($callbackQueryId, $chatId)) {
            return false;
        }

        $message = '';

        switch ($callbackData) {
            case 'join_trello':
                if ($this->db->userHasEmail($userId)) {
                    $message = $this->sendInvitationToTrello($userId);
                } else {
                    $message = 'У Вашому обліковому записі не вказано email. Зарееструйте його для продовження роботи.';
                }
                break;
        }

        $this->telegramBot->sendAnswerCallbackQuery($callbackQueryId, '');
        $this->telegramBot->sendMessage($chatId, $message);
        return false;
    }

    private function sendInvitationToTrello(int $userId): string
    {
        $message = '';

        $email = $this->db->getEmailByTelegramId($userId);
        if ($this->trelloAPI->inviteToBoardViaEmail($email)) {
            $message = 'Запит на приєднання до дошки Trello відправленний на Ваш email.';
            $message .= "\nПосилання на дошку " . $this->trelloAPI->getBoardLink();
        } else {
            $message = 'Запит на приєднання до дошки Trello не був надісланий. Можливо ви вже є її участником.';
            $message .= "\nПосилання на дошку " . $this->trelloAPI->getBoardLink();
        }

        $trelloId = $this->trelloAPI->getMemberIdByEmail($email);
        if ($trelloId) {
            $this->db->addTrelloId($userId, $trelloId);
        }

        return $message;
    }

    private function startSession(int $chatId, int $userId)
    {
        session_id($chatId . '-' . $userId);
        session_start();
    }
}