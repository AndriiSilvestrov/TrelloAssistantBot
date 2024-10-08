#!/bin/bash

# Задаємо змінні
APP_DIR=
DOMAIN=

# Перевіряємо, чи передана опція --dry-run
DRY_RUN=""
if [[ "$1" == "--dry-run" ]]; then
    DRY_RUN="--dry-run"
fi

# Запускаємо Docker з certbot
sudo docker run -it --rm --name certbot \
    -v "/etc/letsencrypt:/etc/letsencrypt" \
    -v "/var/lib/letsencrypt:/var/lib/letsencrypt" \
    -v "$APP_DIR:/var/www/html" \
    certbot/certbot certonly --webroot -w /var/www/html -d $DOMAIN $DRY_RUN