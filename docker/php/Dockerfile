FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    linux-headers \
    autoconf \
    gcc \
    g++ \
    make \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl gd opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

COPY --chown=www:www . .

USER www

EXPOSE 9000
CMD ["php-fpm"]
