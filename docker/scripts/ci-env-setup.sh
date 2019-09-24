#!/usr/bin/env bash

##############################################################
# FUNCTIONS declarations
##############################################################

function Print_CI_Message()
{
    printf '%*s\n' "80" '' | tr ' ' -
    echo $1
    printf '%*s\n' "80" '' | tr ' ' -
}

##############################################################
# End of FUNCTIONS declarations
##############################################################

DEFAULT_ENV="development"

declare -A BRANCH_TO_ENVIRONMENT_MAP=(
    ["dev"]="development"
    ["staging"]="staging"
    ["training"]="training"
    ["beta"]="beta"
    ["master"]="production"
)

if [[ ! -z ${BRANCH_TO_ENVIRONMENT_MAP[${CI_COMMIT_REF_NAME}]} ]]; then
    export ENVIRONMENT=${BRANCH_TO_ENVIRONMENT_MAP[${CI_COMMIT_REF_NAME}]}
else
    export ENVIRONMENT=${DEFAULT_ENV}
fi

export -f Print_CI_Message
