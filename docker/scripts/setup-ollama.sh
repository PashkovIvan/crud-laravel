#!/bin/bash

echo "🚀 Настройка Ollama..."

TIMEOUT=30
ELAPSED=0

echo "⏳ Проверка готовности Ollama..."
while ! docker-compose exec -T ollama ollama list > /dev/null 2>&1; do
    if [ $ELAPSED -ge $TIMEOUT ]; then
        echo "❌ Ollama не доступен. Убедитесь, что контейнер запущен: docker-compose up -d ollama"
        exit 1
    fi
    sleep 2
    ELAPSED=$((ELAPSED + 2))
    echo "   Ожидание... (${ELAPSED}/${TIMEOUT} секунд)"
done

echo "✅ Ollama готов"
echo "📥 Загрузка модели llama3.2 (это может занять несколько минут)..."

if docker-compose exec -T ollama ollama pull llama3.2; then
    echo "✅ Модель llama3.2 успешно загружена"
else
    echo "❌ Ошибка при загрузке модели"
    exit 1
fi

echo "✅ Ollama готов к работе"

