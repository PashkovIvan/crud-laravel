FROM php:8.4-fpm

# Установка системных зависимостей
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

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Установка Node.js и npm
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Создание пользователя для приложения
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Копирование существующих прав доступа из хоста
COPY --chown=www:www . /var/www

# Переключение на пользователя www
USER www

# Установка рабочей директории
WORKDIR /var/www

# Копирование composer файлов
COPY --chown=www:www composer.json composer.lock ./

# Установка зависимостей PHP
RUN composer install --no-dev --optimize-autoloader

# Копирование остальных файлов
COPY --chown=www:www . .

# Установка зависимостей Node.js
RUN npm install && npm run build

# Открытие порта
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"]
