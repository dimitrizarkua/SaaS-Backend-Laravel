# Develop with your favorite IDE and all required software installed manually

------------------
## Requirements

1. PHP 7.2 (~7.2)
1. PHP extensions: gd, mbstring, intl, pdo_sqlite, pdo_pgsql, zip
curl, json, opcache, pcntl, bcmath, sockets, gmp
1. [Composer](https://getcomposer.org/doc/00-intro.md)
1. [PostgreSQL](http://www.postgresql.org/) (~10.3)
1. [RabbitMQ](https://www.rabbitmq.com/) (~3.6)
1. [Redis](https://redis.io/) (~4.0)

------------------
## Installation

1. Install PHP 7.2 and extensions
    ```bash
    sudo apt-get install -y php7.2 php7.2-fpm php7.2-cli php7.2-gd \
    php7.2-mbstring php7.2-intl php7.2-pgsql php7.2-json \
    php7.2-zip php7.2-curl php7.2-json php7.2-opcache php7.2-bcmath \
    php7.2-common
    ```

1. Install and setup Postgres (enter password `steamatic` when prompted)
    ```bash
    sudo apt-get install -y postgresql
    sudo -u postgres createuser steamatic -d -l -P
    sudo -u postgres createdb -O steamatic steamatic_nis
    ```

1. Install and setup RabbitMQ
     ```bash
    sudo apt-get install -y rabbitmq-server
    sudo rabbitmq-plugins enable rabbitmq_management
    sudo rabbitmqctl add_user steamatic steamatic
    sudo rabbitmqctl set_permissions steamatic ".*" ".*" ".*"
    ```
    RabbitMQ management console will be available [here](http://127.0.0.1:15672/).
    Default credentials are *guest*/*guest*.

1. Install Redis
    ```bash
    sudo apt-get install -y redis-server
    ```

1. Clone this repo and `cd` to the folder with cloned repo

1. Download [Composer](https://getcomposer.org/download/) and 
[install globally](https://getcomposer.org/doc/00-intro.md#globally) or update 
`composer self-update`.

1. Install project dependencies
    ```bash
    composer install --no-interaction
    ```

1. Run database schema migrations
    ```bash
    php artisan migrate
    ```

------------------
## Run

Run built-in HTTP server
```bash
php artisan serve
```

You should now be able to visit [localhost:8000](http://localhost:8000) and see
the traffic lights.

------------------
## Unit Testing

1. Create test database in postgres
    ```bash
    sudo -u postgres createdb -O steamatic steamatic_nis_test 
    ```

1. Copy `.env.testing.example` to `.env.testing`
    ```bash
    cp .env.testing.example .env.testing
    ```

1. Run unit tests
    ```bash
    APP_DEBUG=true APP_ENV=testing ./vendor/bin/phpunit
    ```
