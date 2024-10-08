<?php

namespace App;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct($dbPath)
    {
        $this->pdo = new PDO('sqlite:' . $dbPath);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function createTable()
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                telegram_id INTEGER NOT NULL UNIQUE,
                email TEXT DEFAULT NULL,
                trello_id TEXT DEFAULT NULL
            );
        ");
    }

    public function addUser(int $telegramId)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (telegram_id) VALUES (?);");
        $stmt->execute([$telegramId]);
    }

    public function addEmail(int $telegramId, string $email)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET email = ? WHERE telegram_id = ?;");
        $stmt->execute([$email, $telegramId]);
    }

    public function addTrelloId(int $telegramId, string $trelloId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET trello_id = ? WHERE telegram_id = ?;");
        $stmt->execute([$trelloId, $telegramId]);
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users;');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmailByTelegramId(int $telegramId): string|false
    {
        $stmt = $this->pdo->prepare("SELECT email FROM users WHERE telegram_id = ?;");
        $stmt->execute([$telegramId]);
        return $stmt->fetchColumn();
    }

    public function getTrelloIdByTelegramId(int $telegramId): string|false
    {
        $stmt = $this->pdo->prepare("SELECT trello_id FROM users WHERE telegram_id = ?;");
        $stmt->execute([$telegramId]);
        return $stmt->fetchColumn();
    }

    public function userExists(int $telegramId): bool
    {
        static $result;

        if (empty($result)) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE telegram_id = ?;");
            $stmt->execute([$telegramId]);
            $result = $stmt->fetchColumn() > 0;
        }

        return $result;
    }

    public function userHasEmail(int $telegramId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE telegram_id = ? AND email IS NOT NULL;");
        $stmt->execute([$telegramId]);
        return $stmt->fetchColumn() > 0;
    }

    public function userHasTrelloId(int $telegramId): bool
    {
        static $result;

        if (empty($result)) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE telegram_id = ? AND trello_id IS NOT NULL;");
            $stmt->execute([$telegramId]);
            $result = $stmt->fetchColumn() > 0;
        }

        return $result;
    }
}