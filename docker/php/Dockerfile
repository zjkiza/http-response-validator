FROM php:8.4-cli

ARG SYSTEM_TIMEZONE=UTC
ARG APP_ENV=dev
ARG PHP_XDEBUG_ENABLE=1
ARG PHP_XDEBUG_MODE=debug
ARG PHP_XDEBUG_REMOTE_PORT=9003
ARG PHP_XDEBUG_REMOTE_ADDRESS=host.docker.internal
ARG PHP_XDEBUG_IDEKEY=PHPSTORM
ARG PHP_IDE_CONFIG="serverName=localhost"
ARG UID=1000
ARG GID=1000

ENV TZ=${SYSTEM_TIMEZONE} \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    PATH=/composer/vendor/bin:$PATH \
    PHP_XDEBUG_ENABLE=${PHP_XDEBUG_ENABLE} \
    PHP_XDEBUG_MODE=${PHP_XDEBUG_MODE} \
    PHP_XDEBUG_REMOTE_PORT=${PHP_XDEBUG_REMOTE_PORT} \
    PHP_XDEBUG_REMOTE_ADDRESS=${PHP_XDEBUG_REMOTE_ADDRESS} \
    PHP_XDEBUG_IDEKEY=${PHP_XDEBUG_IDEKEY} \
    PHP_IDE_CONFIG=${PHP_IDE_CONFIG} \
    SYSTEM_TIMEZONE=${SYSTEM_TIMEZONE}

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    zip \
    openssl \
    openssh-client \
    gettext-base \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libxml2-dev \
    libxslt-dev \
    zlib1g-dev \
    build-essential \
    xsltproc \
    net-tools \
    nano \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure intl \
 && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    gettext \
    intl \
    zip \
    soap \
    simplexml \
    pcntl \
    xsl

RUN pecl install apcu \
    xdebug \
 && docker-php-ext-enable apcu

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
 && echo ${TZ} > /etc/timezone

COPY docker/php/conf /tmp
RUN chmod +x /tmp/configure.sh && /tmp/configure.sh && rm -rf /tmp/*

#   Holds a raised container
COPY docker/php/cmd.sh /usr/local/bin/cmd.sh
RUN chmod +x /usr/local/bin/cmd.sh

RUN groupadd -g ${GID} app \
 && useradd -u ${UID} -g app -m app

USER app

WORKDIR /www

COPY --chown=app:app composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader
COPY --chown=app:app . .
RUN composer dump-autoload --optimize

CMD ["/usr/local/bin/cmd.sh"]