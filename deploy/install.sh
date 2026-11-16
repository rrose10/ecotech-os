#!/bin/sh
# One-command install for EcoTech OS on a NAS (Docker).
# Usage:  sh deploy/install.sh [LAN-IP-or-hostname]   e.g.  sh deploy/install.sh 192.168.10.154
set -e
cd "$(dirname "$0")/.."

HOST="${1:-$(hostname -I 2>/dev/null | awk '{print $1}')}"
PORT="${APP_PORT:-8088}"

command -v docker >/dev/null 2>&1 || { echo "Docker is not installed. Install it from OMV: System > omv-extras > Docker."; exit 1; }
docker compose version >/dev/null 2>&1 || { echo "Docker Compose plugin missing. Install it from OMV omv-extras (Docker)."; exit 1; }

if [ ! -f .env.docker ]; then
  DB_PASSWORD="$(openssl rand -hex 16)"
  DB_ROOT_PASSWORD="$(openssl rand -hex 16)"
  APP_KEY="base64:$(openssl rand -base64 32)"
  cat > .env.docker <<ENVEOF
APP_NAME="EcoTech OS"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=http://${HOST}:${PORT}
APP_TIMEZONE=Asia/Dhaka
FORCE_HTTPS=false

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ecotech_os
DB_USERNAME=ecotech
DB_PASSWORD=${DB_PASSWORD}
DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD}
DB_DUMP_SKIP_SSL=true

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=false
CACHE_STORE=database
QUEUE_CONNECTION=database

SSL_MODE=off
ENVEOF
  chmod 600 .env.docker
  echo "Created .env.docker with fresh secrets."
else
  echo ".env.docker already exists, keeping it."
fi

mkdir -p backups
chmod 777 backups

# docker compose reads DB_* for the db container from the same file
# Build with the NAS's own network (container NAT on this NAS blocks apt/npm during builds)
docker build --allow network.host --network=host -t ecotech-os:latest ./backend

docker compose --env-file .env.docker up -d

echo "Waiting for the app to start..."
for i in $(seq 1 60); do
  if docker compose --env-file .env.docker exec -T app php artisan migrate:status >/dev/null 2>&1; then
    break
  fi
  sleep 3
done

docker compose --env-file .env.docker exec -T app php artisan ecotech:install

echo
echo "Done. Open  http://${HOST}:${PORT}  and log in with the Super Admin details above."
