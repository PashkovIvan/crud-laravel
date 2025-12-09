# Определение команды docker-compose (поддержка старой и новой версии)
DOCKER_COMPOSE := $(shell which docker-compose 2>/dev/null || echo "docker compose")

.PHONY: help build rebuild test up down logs logs-follow logs-all shell migrate fresh clean install cache-clear

help: ## Показать справку по командам
	@echo "Доступные команды:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Собрать и запустить проект (первая установка)
	@echo "🚀 Начинаем установку проекта Task Manager..."
	@if [ ! -f .env ]; then \
		echo "📄 Копирование .env файла..."; \
		cp .env.example .env; \
	fi
	@echo "🧹 Очистка неиспользуемых ресурсов Docker..."
	@docker system prune -f
	@echo "🏗️  Сборка и запуск контейнеров..."
	@$(DOCKER_COMPOSE) up -d --build
	@echo "⏳ Ожидание готовности PostgreSQL..."
	@TIMEOUT=60; \
	ELAPSED=0; \
	while ! $(DOCKER_COMPOSE) exec -T db pg_isready -U task_user > /dev/null 2>&1; do \
		if [ $$ELAPSED -ge $$TIMEOUT ]; then \
			echo "❌ Таймаут ожидания готовности PostgreSQL ($$TIMEOUT секунд)"; \
			exit 1; \
		fi; \
		sleep 2; \
		ELAPSED=$$((ELAPSED + 2)); \
		echo "   Ожидание... ($$ELAPSED/$$TIMEOUT секунд)"; \
	done
	@echo "✅ PostgreSQL готов"
	@echo "📦 Установка зависимостей Composer..."
	@$(DOCKER_COMPOSE) exec -T app composer install || $(DOCKER_COMPOSE) exec -T app composer update
	@echo "🔑 Генерация ключа приложения..."
	@$(DOCKER_COMPOSE) exec -T app php artisan key:generate
	@echo "🔄 Выполнение миграций..."
	@$(DOCKER_COMPOSE) exec -T app php artisan migrate
	@echo "🧹 Очистка кэша..."
	@$(DOCKER_COMPOSE) exec -T app php artisan cache:clear
	@$(DOCKER_COMPOSE) exec -T app php artisan config:clear
	@$(DOCKER_COMPOSE) exec -T app php artisan route:clear
	@$(DOCKER_COMPOSE) exec -T app php artisan view:clear
	@echo "⏳ Ожидание готовности Ollama..."
	@TIMEOUT=60; \
	ELAPSED=0; \
	while ! $(DOCKER_COMPOSE) exec -T ollama ollama list > /dev/null 2>&1; do \
		if [ $$ELAPSED -ge $$TIMEOUT ]; then \
			echo "⚠️  Таймаут ожидания готовности Ollama ($$TIMEOUT секунд)"; \
			echo "⚠️  Модель не загружена. Вы можете загрузить ее позже командой: make ollama-setup"; \
			break; \
		fi; \
		sleep 2; \
		ELAPSED=$$((ELAPSED + 2)); \
		echo "   Ожидание готовности Ollama... ($$ELAPSED/$$TIMEOUT секунд)"; \
	done; \
	if [ $$ELAPSED -lt $$TIMEOUT ]; then \
		echo "✅ Ollama готов"; \
		echo "📥 Загрузка модели llama3.2 (это может занять несколько минут)..."; \
		$(DOCKER_COMPOSE) exec -T ollama ollama pull llama3.2 && echo "✅ Модель успешно загружена!" || echo "⚠️  Не удалось загрузить модель. Вы можете сделать это позже командой: make ollama-setup"; \
	fi
	@echo "✨ Установка завершена!"
	@echo "🌐 Проект доступен по адресу: http://localhost:8080"

rebuild: ## Полная пересборка проекта (удаление всех данных)
	@echo "🔄 Начинаем полную пересборку проекта Task Manager..."
	@echo "🛑 Остановка и удаление контейнеров, образов и томов..."
	@$(DOCKER_COMPOSE) down --rmi all --volumes
	@$(MAKE) build

up: ## Запустить контейнеры
	@$(DOCKER_COMPOSE) up -d
	@echo "✅ Контейнеры запущены"

down: ## Остановить контейнеры
	@$(DOCKER_COMPOSE) down
	@echo "✅ Контейнеры остановлены"

test: ## Запустить тесты
	@echo "🚀 Начинаем тестирование Task Manager..."
	@if ! $(DOCKER_COMPOSE) ps | grep -q "app.*running"; then \
		echo "❌ Приложение не запущено. Запустите проект командой: make build"; \
		exit 1; \
	fi
	@if ! $(DOCKER_COMPOSE) ps | grep -q "db.*running"; then \
		echo "❌ PostgreSQL не запущен. Запустите проект командой: make build"; \
		exit 1; \
	fi
	@echo "✓ Все сервисы запущены"
	@echo "🧹 Очистка логов..."
	@$(DOCKER_COMPOSE) exec -T app truncate -s 0 storage/logs/laravel.log 2>/dev/null || \
	$(DOCKER_COMPOSE) exec -T app sh -c "echo '' > storage/logs/laravel.log" 2>/dev/null || true
	@echo "🧪 Запуск тестов..."
	@$(DOCKER_COMPOSE) exec -T app php artisan test || (echo "❌ Некоторые тесты не прошли" && echo "" && echo "📋 Последние записи лога Laravel:" && $(DOCKER_COMPOSE) exec -T app tail -n 50 storage/logs/laravel.log 2>/dev/null || echo "Логи недоступны" && echo "" && exit 1)
	@echo "✅ Все тесты прошли успешно"

logs: ## Показать логи приложения
	@echo "📋 Логи приложения:"
	@$(DOCKER_COMPOSE) logs --tail=100 app

logs-follow: ## Показать последние 30 логов и следить в реальном времени
	@echo "📋 Логи приложения (последние 30 строк, режим слежения):"
	@$(DOCKER_COMPOSE) logs --tail=30 -f app

logs-all: ## Показать логи всех сервисов
	@echo "📋 Логи всех сервисов:"
	@$(DOCKER_COMPOSE) logs --tail=100

shell: ## Войти в контейнер приложения
	@$(DOCKER_COMPOSE) exec app bash

migrate: ## Выполнить миграции
	@$(DOCKER_COMPOSE) exec -T app php artisan migrate
	@echo "✅ Миграции выполнены"

fresh: ## Выполнить свежие миграции (с удалением данных)
	@$(DOCKER_COMPOSE) exec -T app php artisan migrate:fresh
	@echo "✅ Свежие миграции выполнены"

clean: ## Очистить кэш и логи
	@$(DOCKER_COMPOSE) exec -T app php artisan cache:clear
	@$(DOCKER_COMPOSE) exec -T app php artisan config:clear
	@$(DOCKER_COMPOSE) exec -T app php artisan route:clear
	@$(DOCKER_COMPOSE) exec -T app php artisan view:clear
	@echo "✅ Кэш очищен"

cache-clear: ## Очистить весь кэш приложения (включая Redis)
	@$(DOCKER_COMPOSE) exec -T app php artisan cache:clear-all
	@echo "✅ Весь кэш очищен"

install: build ## Алиас для build (для совместимости)

ollama-setup: ## Настроить Ollama (загрузить модель)
	@echo "⏳ Проверка готовности Ollama..."
	@TIMEOUT=30; \
	ELAPSED=0; \
	while ! $(DOCKER_COMPOSE) exec -T ollama ollama list > /dev/null 2>&1; do \
		if [ $$ELAPSED -ge $$TIMEOUT ]; then \
			echo "❌ Ollama не доступен. Убедитесь, что контейнер запущен: make up"; \
			exit 1; \
		fi; \
		sleep 2; \
		ELAPSED=$$((ELAPSED + 2)); \
		echo "   Ожидание... ($$ELAPSED/$$TIMEOUT секунд)"; \
	done
	@echo "✅ Ollama готов"
	@echo "📥 Загрузка модели llama3.2 (это может занять несколько минут)..."
	@$(DOCKER_COMPOSE) exec -T ollama ollama pull llama3.2
	@echo "✅ Модель успешно загружена!"

