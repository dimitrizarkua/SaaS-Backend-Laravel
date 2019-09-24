#!/bin/bash

##############################################################
# VARIABLES declarations
##############################################################

OAUTH_KEYS_DEFAULT_STORAGE_DIR="${APP_HOME}/storage"
CLOUDFRONT_KEYS_STORAGE_DIR="${APP_HOME}/cloudfront-keys"
APP_ENV_FILE="${APP_HOME}/.env.${APP_ENV}"

##############################################################
# End of VARIABLES declarations
##############################################################

##############################################################
# FUNCTIONS declarations
##############################################################

abort()
{
    echo $1 >&2
    exit $2
}

function Wait_For_Postgres() {
    echo "Checking if postgres is up..."

    DB_HOST=${DB_HOST:-postgres}
    DB_PORT=${DB_PORT:-5432}
    DB_USERNAME=${DB_USERNAME:-steamatic}
    DB_PASSWORD=${DB_PASSWORD:-steamatic}
    DB_DATABASE=${DB_DATABASE:-steamatic_nis}

    export PGHOST="${DB_HOST}"
    export PGPORT="${DB_PORT}"
    export PGUSER="${DB_USERNAME}"
    export PGPASSWORD="${DB_PASSWORD}"
    export PGDATABASE="${DB_DATABASE}"

    until psql -c '\l' > /dev/null 2>&1; do
        echo "Postgres' down. Waiting..."
        sleep 1
    done

    unset PGHOST PGPORT PGUSER PGPASSWORD PGDATABASE

    echo "Postgres' up."
}

function Echo_Message()
{
    printf '%*s\n' "80" '' | tr ' ' -
    echo "> $1"
    printf '%*s\n' "80" '' | tr ' ' -
}

function Install_Dependencies()
{
    composer install \
        --dev --prefer-dist --optimize-autoloader \
        --no-progress --no-suggest --no-interaction
}

function Run_Migrations()
{
    php artisan migrate --force
}

function Run_Tests()
{
    exec ./vendor/bin/phpunit
}

function Serve_In_Production()
{
    exec supervisord -c ./supervisor.production.server.conf
}

function Serve_With_Debug()
{
    XDEBUG_HOST=${XDEBUG_HOST:-172.17.0.1}
    XDEBUG_PORT=${XDEBUG_PORT:-9001}

    export PHP_IDE_CONFIG="serverName=Steamatic"
    export XDEBUG_CONFIG="${XDEBUG_CONFIG_BASE} remote_host=${XDEBUG_HOST} remote_port=${XDEBUG_PORT}"

    exec supervisord -c ./supervisor.production.server.conf
}

function Run_Dev_Server()
{
    XDEBUG_HOST=${XDEBUG_HOST:-172.17.0.1}
    XDEBUG_PORT=${XDEBUG_PORT:-9001}

    export PHP_IDE_CONFIG="serverName=Steamatic"
    export XDEBUG_CONFIG="${XDEBUG_CONFIG_BASE} remote_host=${XDEBUG_HOST} remote_port=${XDEBUG_PORT}"

    exec php artisan serve --port=80 --host=0.0.0.0
}

function Run_Consumers()
{
    if [[ ! -z ${DB_CONNECTION} ]] && [[ "${DB_CONNECTION}" != 'null' ]]; then
        Wait_For_Postgres
    fi

    if [[ "${APP_ENV}" != 'local' ]]; then
        Echo_Message 'Setting up environment'
        Setup_Env_For_Production

        Echo_Message 'Setting up passport'
        Setup_Passport_For_Production
    fi

    if [[ -z "$1" ]]; then
        exec supervisord -c ./supervisor.consumers.conf
    else
        exec supervisord -c ./supervisor.consumers.conf $1
    fi
}

function Run_Startup_Tasks_For_Production()
{
    Echo_Message 'Setting up environment'
    Setup_Env_For_Production

    Echo_Message 'Setting up passport'
    Setup_Passport_For_Production

    Echo_Message 'Generating API documentation'
    Generate_Api_Documentation

    Echo_Message 'Checking database connection'
    if [[ ! -z ${DB_CONNECTION} ]] && [[ "${DB_CONNECTION}" != 'null' ]]; then
        Wait_For_Postgres
    fi

    Echo_Message 'Checking Elasticsearch indicies'
    Check_Elastic_Indicies

    Echo_Message 'Running cron in background'
    Run_Cron
}

function Run_Startup_Tasks_For_Development()
{
    Echo_Message 'Installing dependencies'
    Install_Dependencies

    Echo_Message 'Checking database connection'
    Wait_For_Postgres

    Echo_Message 'Running database schema migrations'
    Run_Migrations

    Echo_Message 'Setting up Passport'
    Setup_Passport_For_Development

    Echo_Message 'Checking Elasticsearch indicies'
    Check_Elastic_Indicies
}

function Setup_Env_For_Production()
{
    if [[ ! -z ${PHOTOS_SIGNATURE_PRIVATE_KEY} ]]; then
        local PRIVATE_KEY_FILE_PATH=${CLOUDFRONT_KEYS_STORAGE_DIR}/cloudfront.pkey

        mkdir -p ${CLOUDFRONT_KEYS_STORAGE_DIR}
        echo "${PHOTOS_SIGNATURE_PRIVATE_KEY}" > ${PRIVATE_KEY_FILE_PATH}
        chmod 400 ${PRIVATE_KEY_FILE_PATH}
        chown www-data:www-data ${PRIVATE_KEY_FILE_PATH}

        export PHOTOS_SIGNATURE_PRIVATE_KEY_PATH=${PRIVATE_KEY_FILE_PATH}
    fi

    Discover_Services_For_Production

    cat > ${APP_ENV_FILE} <<EOF
PHOTOS_SIGNATURE_PRIVATE_KEY_PATH=${PRIVATE_KEY_FILE_PATH}
REDIS_HOST=${REDIS_HOST}
REDIS_PORT=${REDIS_PORT}
RABBITMQ_HOST=${RABBITMQ_HOST}
RABBITMQ_PORT=${RABBITMQ_PORT}
EOF
}

function Discover_Services_For_Production()
{
    if [[ ! -z ${HELM_RELEASE_NAME} ]]; then
        # REDIS

        export REDIS_HOST="${HELM_RELEASE_NAME}-redis-master"
        export REDIS_PORT=6379

        # RABBITMQ

        export RABBITMQ_HOST="${HELM_RELEASE_NAME}-rabbitmq"
        export RABBITMQ_PORT=5672
    fi
}

function Ensure_Proper_Permissions_For_OAuth_Keys()
{
    local STORAGE_DIR=${1:-${OAUTH_KEYS_DEFAULT_STORAGE_DIR}}

    declare -A OAUTH_KEYS_PERMISSIONS=(
        ["oauth-private.key"]=400
        ["oauth-public.key"]=600
    )

    for KEY_FILE_NAME in "${!OAUTH_KEYS_PERMISSIONS[@]}"; do
        local PERMISSION=${OAUTH_KEYS_PERMISSIONS[${KEY_FILE_NAME}]}
        local FILE="${STORAGE_DIR}/${KEY_FILE_NAME}"

        if [[ -f ${FILE} ]]; then
            chmod ${PERMISSION} ${FILE}
            chown www-data:www-data ${FILE}
        fi
    done
}

function Setup_Passport_For_Production()
{
    # First of all, we check if keys are provided via mounted folder.
    # Otherwise we assume that keys are provided via env variables.

    # If mounted folder doesn't contain keys, then we generate them and save to that folder,
    # otherwise we instruct passport to load keys from that directory (by setting env variable).

    if [[ ! -z ${OAUTH_KEYS_DIR} ]]; then
        if [[ -z `find ${OAUTH_KEYS_DIR} -type f -name *.key` ]]; then
            php artisan passport:keys > /dev/null
            cp ${OAUTH_KEYS_DEFAULT_STORAGE_DIR}/*.key ${OAUTH_KEYS_DIR}
        fi
        cp ${OAUTH_KEYS_DIR}/*.key ${OAUTH_KEYS_DEFAULT_STORAGE_DIR}
    else
        declare -A OAUTH_KEYS=(
            [OAUTH_PRIVATE_KEY]="oauth-private.key"
            [OAUTH_PUBLIC_KEY]="oauth-public.key"
        )
        for OAUTH_KEY_ENV_VAR_NAME in "${!OAUTH_KEYS[@]}"; do
            local OAUTH_KEY_FILENAME=${OAUTH_KEYS[${OAUTH_KEY_ENV_VAR_NAME}]}
            if [[ ! -z ${!OAUTH_KEY_ENV_VAR_NAME} ]]; then
                echo "${!OAUTH_KEY_ENV_VAR_NAME}" > ${OAUTH_KEYS_DEFAULT_STORAGE_DIR}/${OAUTH_KEY_FILENAME}
            fi
        done
    fi

    Ensure_Proper_Permissions_For_OAuth_Keys

    if [[ ! -z ${DB_CONNECTION} ]] && [[ "${DB_CONNECTION}" != 'null' ]]; then
        php artisan oauth:setup > /dev/null
    fi
}

function Setup_Passport_For_Development()
{
    php artisan passport:keys > /dev/null
    php artisan oauth:setup > /dev/null

    Ensure_Proper_Permissions_For_OAuth_Keys
}

function Generate_Api_Documentation()
{
    php artisan l5-swagger:generate
}

function Check_Elastic_Indicies()
{
    php artisan elastic:check-indexes
}

function Run_Cron()
{
    mkdir -p /var/log/cron
    crond -b -L /var/log/cron/cron.log
}

##############################################################
# End of FUNCTIONS declarations
##############################################################
