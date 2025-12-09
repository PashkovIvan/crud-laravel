#!/bin/bash

# Проверка наличия Docker
if ! command -v docker &> /dev/null; then
    echo "🚫 Docker не установлен. Пожалуйста, установите Docker."
    exit 1
fi

# Проверка наличия Docker Compose
if ! command -v docker-compose &> /dev/null; then
    echo "🚫 Docker Compose не установлен. Пожалуйста, установите Docker Compose."
    exit 1
fi

echo "🚀 Начинаем установку проекта Task Manager..."

# Копирование .env файла
if [ ! -f .env ]; then
    echo "📄 Копирование .env файла..."
    cp .env.example .env
else
    echo "ℹ️  Файл .env уже существует, пропускаем копирование"
fi

# Очистка неиспользуемых ресурсов Docker
echo "🧹 Очистка неиспользуемых ресурсов Docker..."
docker system prune -f

# Сборка и запуск контейнеров
echo "🏗️  Сборка и запуск контейнеров..."
docker-compose up -d --build

# Ожидание готовности базы данных
echo "⏳ Ожидание готовности PostgreSQL..."
TIMEOUT=60
ELAPSED=0
while ! docker-compose exec -T db pg_isready -U task_user > /dev/null 2>&1; do
    if [ $ELAPSED -ge $TIMEOUT ]; then
        echo "❌ Таймаут ожидания готовности PostgreSQL (${TIMEOUT} секунд)"
        exit 1
    fi
    sleep 2
    ELAPSED=$((ELAPSED + 2))
    echo "   Ожидание... (${ELAPSED}/${TIMEOUT} секунд)"
done
echo "✅ PostgreSQL готов"

# Установка зависимостей
echo "📦 Установка зависимостей Composer..."
if ! docker-compose exec -T app composer install; then
    echo "📦 Установка зависимостей не удалась, пробуем обновить..."
    if ! docker-compose exec -T app composer update; then
        echo "❌ Ошибка установки зависимостей Composer."
        exit 1
    fi
fi

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
docker-compose exec -T app php artisan key:generate || {
    echo "❌ Ошибка генерации ключа приложения."
    exit 1
}

# Миграции
echo "🔄 Выполнение миграций..."
docker-compose exec -T app php artisan migrate || {
    echo "❌ Ошибка выполнения миграций."
    exit 1
}

# Очистка кэша
echo "🧹 Очистка кэша..."
docker-compose exec -T app php artisan cache:clear
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan route:clear
docker-compose exec -T app php artisan view:clear

echo "✨ Установка завершена!"
echo "🌐 Проект доступен по адресу: http://localhost:8080"
echo "🐘 PostgreSQL доступен на порту: 5432"
echo "📊 Redis доступен на порту: 6379"
echo ""
echo "📋 Полезные команды:"
echo ""
echo "Для просмотра логов:"
echo "  docker-compose logs -f app"
echo "  или"
echo "  docker-compose logs --tail=30 -f app"
echo ""
echo "Для запуска тестов:"
echo "  ./test.sh"
echo "  или"
echo "  docker-compose exec -T app php artisan test"
echo ""
echo "Для входа в контейнер:"
echo "  docker-compose exec app bash"

