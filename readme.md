# Track Share
A web app that makes sharing music between streaming providers easier.

Built using [Laravel 5.6](https://laravel.com/docs/5.6)

## Setup

1. `git clone git@github.com:awesomecodegreatjob/track-share.git`
2. `cd track-share`
3. `composer install`
4. `cp .env.example .env`
5. `php artisan key:generate`
6. Open .env, config database settings
7. `php artisan migrate`
8. `php artisan serve`

### Local Development Setup

For convenience, Docker can be used to host locally.

To install using Docker:
1. `docker-compose up -d`
2. `docker exec -it track_share_web /bin/bash`
3. `composer install`
4. `cp .env.example .env`
5. `php artisan key:generate`
6. Open .env, set `DB_CONNECTION` to "docker_mysql"
7. `php artisan migrate`

This will leave you with a Bash session in the web directory.

Open `http://localhost:8080` in your web browser.

## Requirements
* PHP 7.2+
* [Composer](https://getcomposer.org/)
