# Develop with PHPStorm and containerized development environment

------------------
## Requirements

1. [Docker CE](https://www.docker.com/community-edition) (~18.06)
1. [Docker Compose](https://docs.docker.com/compose/) (~1.22)
1. [PHPStorm](https://www.jetbrains.com/phpstorm/) (~2018)

------------------
## Installation

1. Install [Docker CE](https://docs.docker.com/install/linux/docker-ce/)

1. Install [Docker Compose](https://docs.docker.com/compose/install/)

1. Clone this repo and `cd` to the folder with cloned repo

1. Copy `.env.compose.example` to `.env` and edit as required
    ```bash
    cp .env.compose.example .env
    ```

1. Copy `.env.local.example` to `.env.local`
    ```bash
    cp .env.local.example .env.local
    ```

1. Copy `docker-compose-example.yml` to `docker-compose.yml` and edit as required
    ```bash
    cp docker-compose-example.yml docker-compose.yml
    ```
    If you are on Mac, you want to change `XDEBUG_HOST` variable value to 
    `docker.for.mac.host.internal` in `docker-compose.yml`

------------------
## Run

```bash
docker-compose up -d
```

You should now be able to visit [localhost:8000](http://localhost:8000) and see
the traffic lights.

------------------
## PHPStorm configuration

### Setup Debug configuration

`Run` -> `Edit confgurations` -> Add new `PHP Remote Debug` with name **Steamatic Debug**

1. Check `Filter Debug Connections by IDE Key`
1. Enter **PHPSTORM** as `IDE key (session id)`
1. Add new Server with the following parameters: 
   `Name`: **Steamatic**, `Host`: **0.0.0.0**,
   `Port`: **80**, `Debugger`: **XDebug**,
   Check `Use path mappings ...` and map root repo folder to `/app` on server.

`Settings` (For current project) -> `Language & Frameworks` -> `PHP` -> `Debug`

For XDebug section:
1. Enter **9001** as `Debug port`
1. Check `Can accept incomming connections`
1. Uncheck all `Force break...` checkboxes

Navigate down to `DBGp Proxy` and:
1. Enter **PHPSTORM** as `IDE key`
1. Enter **localhost** as `Host`
1. Enter **9001** as `Port`

### Setup Interpreter configuration

`Settings` (For current project) -> `Language & Frameworks` -> `PHP`

1. Add new `Interpreter` from `Docker-compose` with name `Steamatic PHP 7.2 Docker (Backend)`
1. Enter **backend-interpreter** as `Service`
1. Add your local docker installation as `Server` with connection through unixsocket
1. Check `Visible only for this project`
1. Add environment variable **APP_ENV=local** **APP_DEBUG=true**

### Setup Test configuration

`Run` -> `Edit confgurations` -> Add new `PHPUnit` with name **Steamatic Tests**

1. Check `Single instance only`
1. Select **Defined in configuration file** as `Test scope`
1. Add environment variables **APP_ENV=testing** **APP_DEBUG=true**
1. Add new test framework `PHPUnit by remote interpreter` and select `Steamatic PHP 7.2 Docker (Backend)`
   as interpreter
1. Select `Use Composer autoloader` with `Path to script` set to **./vendor/autoload.php**
1. Specify **./phpunit.xml** as `Default configuration file`

### Setup Code Sniffer configuration

`Settings` (For current project) -> `Language & Frameworks` -> `PHP`

1. Add new `Interpreter` from `Docker` with name `PHP 7.2 Docker (CLI)`
1. Enter **prooph/php:7.2-cli** as `Image name`
1. Add your local docker installation as `Server` with connection through unixsocket
1. Uncheck `Visible only for this project`

`Settings` (For current project) -> `Language & Frameworks` -> `PHP` -> `Code Sniffer`

1. Add new configuration and select **PHP 7.2 Docker (CLI)** as `Interpreter`
1. Setup path to `phpcs` which is located at **./vendor/bin/phpcs** relative where the project
source folder is mounted and press `Validate` button. You should see `phpcs` version as the output.

`Settings` (For current project) -> `Editor` -> `Inspections` -> `PHP` -> `PHP Code Sniffer validation`
1. Set coding standard `PSR-2`

------------------
## Unit Testing

Run unit tests in PHPStorm as you usually do with the configuration added
in `PHPStorm configuration` section.

------------------
## Additional IDE plugins
Please consider installing additional plugins for PHPStorm to simplify
your life:
- .ignore
- .env files support
- Bash Support
- PHP Annotations
- Laravel Plugin

------------------
## Additional notes

Please don't forget that your development environment is containerized,
and if you need for example to install additional compose dependencies then you
need to execute this commands inside container, not locally:
```bash
docker-compose exec backend composer require laravel/passport
```
