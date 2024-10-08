## Resources
**Trello:**  
- Опис початку роботи з Trello API - https://developer.atlassian.com/cloud/trello/guides/rest-api/api-introduction/
- Документація Trello API - https://developer.atlassian.com/cloud/trello/rest/

**Telegram:**  
- Документація Telegram API - https://core.telegram.org/bots/api

**Additional:**  
- Сайт для тестування Webhook - https://webhook.site/


## Snippets
**Trello:**  

Створення Webhook  

```sh
curl --location 'https://api.trello.com/1/webhooks/' \
--header 'Content-Type: application/json' \
--data '{
    "callbackURL": "{{ SITE_URL_FOR_WEBHOOK }}",
    "idModel": "{{ TRELLO_BOARD_ID }}",
    "key": "{{ TRELLO_API_KEY }}",
    "token": "{{ TRELLO_API_TOKEN }}"
}'
```

Створення нової дошки

```sh
curl --location 'https://api.trello.com/1/boards/' \
--header 'Content-Type: application/json' \
--data '{
    "name": "{{ BOARD_NAME }}",
    "key": "{{ TRELLO_API_KEY }}",
    "token": "{{ TRELLO_API_TOKEN }}",
    "defaultLists": false
}'
```

Створення нового списку (колонки)  

```sh
curl --location 'https://api.trello.com/1/lists' \
--header 'Content-Type: application/json' \
--data '{
    "name": "{{ LIST_NAME }}", 
    "idBoard": "{{ TRELLO_BOARD_ID }}",
    "pos": "bottom",
    "key": "{{ TRELLO_API_KEY }}",
    "token": "{{ TRELLO_API_TOKEN }}"
}'
```

Створення нової картки

```sh
curl --location 'https://api.trello.com/1/cards' \
--header 'Content-Type: application/json' \
--data '{
    "idList": "{{ TRELLO_LIST_ID }}",
    "name": "{{ CARD_TITLE }}",
    "desc": "{{ CARD_DESCRIPTION }}",
    "key": "{{ TRELLO_API_KEY }}",
    "token": "{{ TRELLO_API_TOKEN }}"
}'
```

**Telegram:**  

Створення Webhook  

```sh
curl --location 'https://api.telegram.org/bot{{ TELEGRAM_API_TOKEN }}/setWebhook?url={{ SITE_URL_FOR_WEBHOOK }}'
```