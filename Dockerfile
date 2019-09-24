################################################################################
# SUPERVISORD BUILDER
################################################################################

FROM golang:1.11-alpine AS supervisor-builder

ARG SUPERVISOR_COMMIT_SHA="c613a3f8bd5a078d1d4a7c732c1c0fe018919eda"

RUN apk add --no-cache --update git

RUN go get -v -u github.com/ochinchina/supervisord

RUN cd ${GOPATH}/src/github.com/ochinchina/supervisord \
    && git checkout ${SUPERVISOR_COMMIT_SHA}

RUN CGO_ENABLED=0 GOOS=linux go build -a -ldflags "-extldflags -static" \
    -o /usr/local/bin/supervisord github.com/ochinchina/supervisord

################################################################################
# CADDY BUILDER
################################################################################

FROM golang:1.12-alpine as caddy-builder

ENV GO111MODULE=on

RUN apk add --no-cache git

RUN mkdir -p ./caddy

WORKDIR ./caddy

COPY docker/build/caddy .

RUN go build

RUN mv caddy /usr/bin/caddy

################################################################################
# BASE IMAGE
################################################################################

FROM php:7.3-fpm-alpine3.8 as base

LABEL maintainer="Quantumsoft LLC"

# ------------------------------------------------------------------------------
# Install PHP extensions and persistent system deps
# ------------------------------------------------------------------------------

ENV PHP_REDIS_VERSION=4.2.0 \
    PHP_IMAGICK_VERSION=3.4.3 \
    PHP_MAILPARSE_VERSION=3.0.3

ENV EXTENSIONS_DEPS \
    git \
    libzip-dev \
    postgresql-dev \
    sqlite-dev \
    libressl-dev \
    curl-dev \
    icu-dev \
    zlib-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libxml2-dev \
    imagemagick-dev \
    libtool \
    gmp-dev \
    libzip-dev

ENV BUILD_DEPS="${PHPIZE_DEPS} ${EXTENSIONS_DEPS}"

ENV PERSISTENT_DEPS \
    bash \
    git \
    libzip \
    tini \
    libpq \
    icu-libs \
    freetype \
    libpng \
    libjpeg-turbo \
    imagemagick \
    postgresql-client \
    gmp \
    curl \
    ca-certificates \
    openssh-client

ENV CORE_EXTENSIONS \
    gd \
    mbstring \
    intl \
    pdo_sqlite \
    pdo_pgsql \
    zip \
    curl \
    json \
    pcntl \
    bcmath \
    sockets \
    gmp

ENV PECL_EXTENSIONS_TO_BE_INSTALLED \
    imagick-${PHP_IMAGICK_VERSION} \
    redis-${PHP_REDIS_VERSION} \
    mailparse-${PHP_MAILPARSE_VERSION}

ENV PECL_EXTENSIONS_TO_BE_ENABLED \
    imagick \
    redis \
    mailparse

RUN set -xe \
    && apk add --no-cache --update --virtual .build-deps ${BUILD_DEPS} \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_pgsql --with-pgsql \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure gd \
        --with-gd \
        --with-png-dir=/usr/include/ \
        --with-freetype-dir=/usr/include/ \
        --with-jpeg-dir=/usr/include/ \
        --enable-gd-native-ttf \
    && docker-php-ext-install -j$(nproc) ${CORE_EXTENSIONS} \
    && pecl install ${PECL_EXTENSIONS_TO_BE_INSTALLED} \
    && docker-php-ext-enable ${PECL_EXTENSIONS_TO_BE_ENABLED} \
    && apk del .build-deps \
    && apk add --no-cache --virtual .persistent-php-deps ${PERSISTENT_DEPS} \
    && rm -rf /tmp/pear \
    && rm -rf /var/cache/apk \
    && rm -rf /var/lib/apk \
    && rm -rf /etc/apk/cache

# ------------------------------------------------------------------------------
# Install supervisor
# ------------------------------------------------------------------------------

#COPY --from=ochinchina/supervisord:latest /usr/local/bin/supervisord /usr/bin/supervisord
COPY --from=supervisor-builder /usr/local/bin/supervisord /usr/bin/supervisord

# ------------------------------------------------------------------------------
# Install Composer
# ------------------------------------------------------------------------------

RUN curl -sS https://getcomposer.org/installer | php -- \
        --filename=composer \
        --install-dir=/usr/bin

# The following is too slow cos composer is not based on alpine :(
# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ------------------------------------------------------------------------------
# Setup environment and workdir
# ------------------------------------------------------------------------------

ENV APP_HOME=/app

WORKDIR ${APP_HOME}

# ------------------------------------------------------------------------------
# Install compose deps
# ------------------------------------------------------------------------------

COPY ./composer.json ./composer.lock ./

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN composer global require "hirak/prestissimo:^0.3" \
        --prefer-dist --classmap-authoritative \
        --no-progress --no-suggest \
    && composer install \
        --no-dev --prefer-dist \
        --no-progress --no-suggest \
        --no-autoloader --no-interaction \
    && composer clearcache

# ------------------------------------------------------------------------------
# Supply php and php-fpm with custom configs from the repo
# ------------------------------------------------------------------------------

COPY docker/config/php/php.ini /usr/local/etc/php/
COPY docker/config/php/php7.ini /usr/local/etc/php/conf.d/
COPY docker/config/php/fpm/php-fpm.conf /usr/local/etc/
COPY docker/config/php/fpm/pool.d /usr/local/etc/pool.d

# ------------------------------------------------------------------------------
# Copy source code to the workdir and setup required folders
# ------------------------------------------------------------------------------

COPY ./ ./

RUN mkdir -p \
    ./storage/app/public \
    ./storage/framework/cache \
    ./storage/framework/sessions \
    ./storage/framework/testing \
    ./storage/framework/views \
    ./storage/logs \
    ./bootstrap/cache

RUN chown www-data:www-data -R ./storage ./bootstrap/cache && \
    chmod g+rw,g+s -R ./storage ./bootstrap/cache

# ------------------------------------------------------------------------------
# Optimize autoloader
# ------------------------------------------------------------------------------

RUN composer dump-autoload --optimize

# ------------------------------------------------------------------------------
# Add crontab entry for Laravel Scheduler
# ------------------------------------------------------------------------------

RUN mkdir -p /var/log/cron \
    && echo "* * * * * php ${APP_HOME}/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontabs/root

# ------------------------------------------------------------------------------
# Expose HTTP port
# ------------------------------------------------------------------------------

EXPOSE 80

# ------------------------------------------------------------------------------
# Install Caddy
# ------------------------------------------------------------------------------

COPY --from=caddy-builder /usr/bin/caddy /usr/bin/caddy

# ------------------------------------------------------------------------------
# Setup entrypoint
# ------------------------------------------------------------------------------

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
RUN ln -s usr/local/bin/docker-entrypoint.sh / # backwards compatibility
ENTRYPOINT ["/sbin/tini", "--", "docker-entrypoint.sh"]

################################################################################
# IMAGE FOR RUNNING UNIT TESTS
################################################################################

FROM base AS test

# ------------------------------------------------------------------------------
# Install composer dev dependencies
# ------------------------------------------------------------------------------

RUN composer install \
    --dev --prefer-dist --no-progress \
    --no-suggest --optimize-autoloader --no-interaction

################################################################################
# IMAGE FOR PRODUCTION
################################################################################

FROM base AS production

# ------------------------------------------------------------------------------
# Install additional extensions
# ------------------------------------------------------------------------------

RUN apk add --no-cache --update --virtual .build-deps ${PHPIZE_DEPS} \
    && docker-php-ext-configure opcache --enable-opcache \
    && docker-php-ext-install opcache \
    && apk del .build-deps \
    && rm -rf /tmp/pear \
    && rm -rf /var/cache/apk \
    && rm -rf /var/lib/apk \
    && rm -rf /etc/apk/cache

COPY docker/config/php/extensions/opcache.ini ${PHP_INI_DIR}/conf.d/

################################################################################
# IMAGE FOR DEVELOPMENT
################################################################################

FROM test AS development

# ------------------------------------------------------------------------------
# Install additional extensions
# ------------------------------------------------------------------------------

ENV PHP_XDEBUG_VERSION 2.7.0

ENV DEV_PECL_EXTENSIONS_TO_BE_INSTALLED \
    xdebug-${PHP_XDEBUG_VERSION}

ENV DEV_PECL_EXTENSIONS_TO_BE_ENABLED \
    xdebug

RUN apk add --no-cache --update --virtual .build-deps ${PHPIZE_DEPS} \
    && pecl install ${DEV_PECL_EXTENSIONS_TO_BE_INSTALLED} \
    && docker-php-ext-enable ${DEV_PECL_EXTENSIONS_TO_BE_ENABLED} \
    && apk del .build-deps \
    && rm -rf /tmp/pear \
    && rm -rf /var/cache/apk \
    && rm -rf /var/lib/apk \
    && rm -rf /etc/apk/cache

ENV XDEBUG_CONFIG_BASE="remote_enable=1 idekey=PHPSTORM zend_extension=xdebug.so"
