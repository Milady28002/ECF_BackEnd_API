FROM dunglas/frankenphp:1-php8.3

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    intl \
    zip \
    opcache \
    mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

COPY Caddyfile /etc/caddy/Caddyfile

ENV APP_ENV=prod

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]