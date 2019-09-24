# Steamatic-NIS Backend

This repository contains Steamatic-NIS backend application sources which 
is built on top of [Laravel](https://laravel.com) framework.

------------------
## Environments

The application is deployed to several environments available on the web:
- **Development** (`dev` branch):
  [API Documentation](http://api.dev.steamatic.com.au/v1/docs),
  [Website](http://dev.steamatic.com.au)
- **Staging** (`staging` branch):
  [API Documentation](http://api.staging.steamatic.com.au/v1/docs),
  [Website](http://staging.steamatic.com.au)

------------------
## Develop locally

There are 2 approaches for local development. You can:
1. [Develop with PHPStorm and containerized development environment](docs/Develop-with-docker.md)
1. [Develop with your favorite IDE and all required software installed manually](docs/Develop-manual-installation.md)

------------------
## Configuration

All of the configuration files for the Laravel framework are stored
in the `config` directory. Each option is documented, so feel free to look
through the files and get familiar with the options available to you.


The following env variables must be passed to backend application when
deployed to live environment:
```bash
APP_KEY
APP_ENV
APP_URL
DB_CONNECTION
DB_USERNAME
DB_PASSWORD
DB_DATABASE
DB_HOST
DB_PORT
RABBITMQ_HOST
RABBITMQ_PORT
RABBITMQ_LOGIN
RABBITMQ_PASSWORD
REDIS_HOST
MAIL_DRIVER
MAIL_FROM_ADDRESS
AWS_ACCESS_KEY_ID
AWS_SECRET_ACCESS_KEY
AWS_REGION
DOCUMENTS_STORAGE_S3_BUCKET_NAME
PHOTOS_STORAGE_S3_BUCKET_NAME
PHOTOS_DISTRIBUTION_BASE_URL
PHOTOS_SIGNATURE_KEY_PAIR_ID
PHOTOS_SIGNATURE_PRIVATE_KEY_PATH
PUSHER_APP_KEY
PUSHER_APP_SECRET
PUSHER_APP_ID
PUSHER_APP_CLUSTER
SCOUT_DRIVER
SCOUT_ELASTIC_HOST
PINPAYMENT_TEST_MODE
```
The following env variables must be passed if `mailgun` driver is used as
`MAIL_DRIVER`:
```bash
MAILGUN_DOMAIN
MAILGUN_SECRET

```
The keys for OAuth 2 server can be supplied to application in one of
the following ways:
1. Specify directory where keys are stored via `OAUTH_KEYS_DIR`
  env variable. If directory doesn't contain the keys, application
  will generate the keys and store them in that folder.
1. Specify private and public keys via env variables `OAUTH_PRIVATE_KEY`
  and `OAUTH_PUBLIC_KEY`.

------------------
## CI/CD

When you push to the branches associated with the live environments
(see `Environments` section above), the backend application 
will be automatically built, tested and deployed to corresponding
live environment if deployment pipeline will succeed.

All other branches in this repo will be built and tested on shared runners when
you push to them.
