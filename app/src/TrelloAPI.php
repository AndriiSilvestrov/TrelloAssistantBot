<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class TrelloAPI
{
    private string $apiKey;
    private string $token;
    private string $boardId;
    private Client $client;

    public function __construct()
    {
        $this->apiKey = $_ENV['TRELLO_API_KEY'];
        $this->token = $_ENV['TRELLO_API_TOKEN'];
        $this->boardId = $_ENV['TRELLO_BOARD_ID'];
        $this->client = new Client(['base_uri' => "https://api.trello.com/1/"]);
    }

    public function getBoardLink(): string
    {
        $response = $this->client->get("boards/{$this->boardId}", [
            'query' => [
                'key' => $this->apiKey, 
                'token' => $this->token, 
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        return $response['shortUrl'];
    }

    public function inviteToBoardViaEmail(string $email): bool
    {
        try {
            $this->client->put("boards/{$this->boardId}/members", [
                'query' => [
                    'key' => $this->apiKey, 
                    'token' => $this->token, 
                    'email' => $email,
                ]
            ]);
        } catch (ClientException $e) {
            return false;
        }

        return true;
    }

    public function getMember(string $trelloID): array
    {
        $responce = $this->client->get("members/{$trelloID}", [
            'query' => [
                'key' => $this->apiKey, 
                'token' => $this->token, 
            ]
        ]);

        return json_decode($responce->getBody(), true);
    }

    public function getMemberIdByEmail(string $email): string|false
    {
        $responce = $this->client->get('search/members/', [
            'query' => [
                'key' => $this->apiKey, 
                'token' => $this->token, 
                'query' => $email,
            ]
        ]);

        return json_decode($responce->getBody(), true)[0]['id'] ?? false;
    }

    public function getCardsOfList(string $listId): array
    {
        $response = $this->client->get("lists/{$listId}/cards", [
            'query' => [
                'key' => $this->apiKey, 
                'token' => $this->token, 
            ]
        ]);
        return json_decode($response->getBody(), true);
    }
}