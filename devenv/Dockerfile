FROM php:8.5-cli AS build

WORKDIR /srv

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer create-project --no-interaction --no-progress laravel/laravel app

COPY . /srv/package
WORKDIR /srv/app
RUN composer config minimum-stability dev \
    && composer config prefer-stable true \
    && composer config repositories.laravel-healthchecks path /srv/package \
    && composer require --no-interaction --no-progress govigilant/laravel-healthchecks:dev-main laravel/horizon \
    && php artisan key:generate \
    && php artisan horizon:install

FROM php:8.5-cli
WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev redis-server supervisor \
    && docker-php-ext-install zip pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=build /srv/app /var/www/html
COPY devenv/supervisord.conf /etc/supervisor/conf.d/healthchecks.conf

RUN mkdir -p /var/log/supervisor

EXPOSE 8000 6379
CMD ["supervisord", "-n"]
