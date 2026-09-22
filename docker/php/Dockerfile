FROM php:8.4-fpm-alpine

ARG UID=1000
ARG GID=1000

# System deps
RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    supervisor \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Samakan UID/GID www-data dengan user host
RUN deluser www-data 2>/dev/null || true; \
    delgroup www-data 2>/dev/null || true; \
    addgroup -g ${GID} www-data && \
    adduser -D -G www-data -u ${UID} -s /bin/sh www-data

WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

USER www-data

EXPOSE 9000

CMD ["php-fpm"]
