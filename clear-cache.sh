#!/usr/bin/env bash

set -Eeuo pipefail

app_root="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
docker_root="$(dirname -- "$app_root")"
restart_services=false
recreate_services=false

case "${1:-}" in
    '')
        ;;
    --restart)
        restart_services=true
        ;;
    --recreate)
        recreate_services=true
        ;;
    *)
        echo "Использование: ./clear-cache.sh [--restart|--recreate]" >&2
        exit 2
        ;;
esac

cd "$docker_root"

echo "Очищаю кеш Laravel в контейнере php-www..."
docker compose exec -T php-www php artisan optimize:clear

if [[ "$restart_services" == true ]]; then
    echo "Перезапускаю php-www для очистки OPcache..."
    docker compose restart php-www

    echo "Перезапускаю nginx-www для обновления адреса php-www..."
    docker compose restart nginx-www
fi

if [[ "$recreate_services" == true ]]; then
    echo "Пересоздаю php-www и nginx-www (на случай зависшего bind-mount)..."
    docker compose up -d --force-recreate php-www nginx-www
fi

echo "Кеш очищен."
