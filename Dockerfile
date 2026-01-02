FROM php:8.4-fpm

# Установка системных зависимостей
# problem: а точно ли все пакеты эти нужны тебе?
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Установка расширения Redis
RUN apt-get install -y $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get remove -y $PHPIZE_DEPS \
    && apt-get autoremove -y

# Установка Composer
# problem: latest - ловушка (вспомни php-клуб, где php 8.5 включилась таким образом)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание пользователя для приложения
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Установка рабочей директории
WORKDIR /var/www

# Копирование composer файлов (для кэширования слоя зависимостей)
# problem: а может composer.lock лучше скопировать для выполнения composer install?
COPY --chown=root:root composer.json ./

# Установка зависимостей PHP (от root для создания composer.lock, без скриптов, т.к. artisan еще не скопирован)
# problem: я бы добавил еще --no-dev
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Изменение владельца файлов на www:www
RUN chown -R www:www /var/www

# Копирование остальных файлов
COPY --chown=www:www . .

# Выполнение скриптов Composer после копирования всех файлов
RUN composer dump-autoload --optimize --no-interaction

# Изменение владельца всех файлов на www:www
# problem: ты выше уже поменял владельца, зачем еще раз?
RUN chown -R www:www /var/www

# Переключение на пользователя www
USER www

# Открытие порта
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"]
