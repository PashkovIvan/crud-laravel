#!/bin/bash

# Цвета для вывода
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}🚀 Начинаем тестирование Task Manager...${NC}"

# Проверяем, запущены ли все сервисы
echo -e "\n${YELLOW}Проверка статуса сервисов...${NC}"
if ! docker-compose ps | grep -q "app.*running"; then
    echo -e "${RED}❌ Приложение не запущено${NC}"
    echo "Запустите проект командой: ./build.sh"
    exit 1
fi

if ! docker-compose ps | grep -q "db.*running"; then
    echo -e "${RED}❌ PostgreSQL не запущен${NC}"
    echo "Запустите проект командой: ./build.sh"
    exit 1
fi

echo -e "${GREEN}✓ Все сервисы запущены${NC}"

# Очищаем логи
echo -e "\n${YELLOW}Очистка логов...${NC}"
docker-compose exec -T app truncate -s 0 storage/logs/laravel.log 2>/dev/null || \
docker-compose exec -T app sh -c "echo '' > storage/logs/laravel.log" 2>/dev/null || \
echo "⚠️  Не удалось очистить логи"
echo -e "${GREEN}✓ Логи очищены${NC}"

# Запускаем тесты
echo -e "\n${YELLOW}Запуск тестов...${NC}"
if docker-compose exec -T app php artisan test; then
    echo -e "\n${GREEN}✅ Все тесты прошли успешно${NC}"
    TEST_RESULT=0
else
    echo -e "\n${RED}❌ Некоторые тесты не прошли${NC}"
    TEST_RESULT=1
fi

# Показываем логи
echo -e "\n${YELLOW}Последние записи лога:${NC}"
docker-compose exec -T app tail -n 20 storage/logs/laravel.log 2>/dev/null || echo "Логи недоступны"

echo -e "\n${YELLOW}Для просмотра полных логов выполните:${NC}"
echo "docker-compose exec app tail -f storage/logs/laravel.log"

exit $TEST_RESULT

