#!/usr/bin/env bash

if [[ $(supervisord ctl status | grep -E -c 'BACKOFF|EXITED|FATAL') -gt 0 ]]; then
    exit 1
fi

exit 0
