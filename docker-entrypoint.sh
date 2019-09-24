#!/usr/bin/env bash

set -e

. ./docker/scripts/entrypoint-helpers.sh

if [[ "${1}" = "test" ]]
then
    export APP_ENV=testing
    export APP_DEBUG=true

    Run_Migrations
    Setup_Passport_For_Development
    Run_Tests
elif [[ "${1}" = "code_sniffer" ]]
then
    composer cs app/ tests/
elif [[ "${1}" = "run_migrations" ]]
then
    Run_Migrations
elif [[ "${1}" = "serve" ]]
then
    Echo_Message 'Running startup tasks'
    Run_Startup_Tasks_For_Production

    Echo_Message 'Launching PHP-FPM in production mode'
    Serve_In_Production
elif [[ "${1}" = "dev_server" ]]
then
    export APP_ENV=local
    export APP_DEBUG=true

    Echo_Message 'Running startup tasks'
    Run_Startup_Tasks_For_Development

    Echo_Message 'Launching dev server'
    Run_Dev_Server
elif [[ "${1}" = "consumers" ]]
then
    Run_Consumers
else
    exec "$@"
fi
