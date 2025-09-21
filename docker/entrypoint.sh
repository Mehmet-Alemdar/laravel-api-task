#!/bin/bash
set -e

echo ">> DB bekleniyor..."

# Wait for database to be ready
until pg_isready -h db -p 5432 -U laravel_user; do
    echo ">> PostgreSQL is unavailable - sleeping"
    sleep 2
done

echo ">> DB hazır!"

# Wait for Redis to be ready
until nc -z redis 6379; do
    echo ">> Redis is unavailable - sleeping"
    sleep 2
done

echo ">> Redis hazır!"

# Generate application key if not exists
if [ ! -f .env ]; then
    echo ">> .env dosyası kopyalanıyor..."
    cp .env.example .env
fi

if [ -z "$APP_KEY" ]; then
    echo ">> Application key oluşturuluyor..."
    php artisan key:generate --no-interaction
fi

# Clear and cache config
echo ">> Config cache oluşturuluyor..."
php artisan config:clear
php artisan config:cache

# Run migrations
echo ">> Migration'lar çalıştırılıyor..."
php artisan migrate --force

# Seed database if needed
if [ "$APP_ENV" != "production" ]; then
    echo ">> Database seed ediliyor..."
    php artisan db:seed --force
fi

# Cache routes and views
echo ">> Route ve view cache oluşturuluyor..."
php artisan route:cache
php artisan view:cache

echo ">> Laravel uygulaması hazır!"

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
