<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Command Project Auth Service

Installs the project dependencies:

``` bash
composer install
```

Set default timezone:

```php
// config/app.php
return [
    // ...

    'timezone' => 'UTC'

    // ...
];
```

Set configuration cache:

```php
// config/cache.php
return [
    // ...

    /*
    |--------------------------------------------------------------------------
    | User Cache TTL
    |--------------------------------------------------------------------------
    |
    | TTL for user cache in seconds.
    |
    */

    'user_ttl' => env('USER_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Role Cache TTL
    |--------------------------------------------------------------------------
    |
    | TTL for role cache in seconds.
    |
    */

    'role_ttl' => env('ROLE_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Permission Cache TTL
    |--------------------------------------------------------------------------
    |
    | TTL for permission cache in seconds.
    |
    */

    'permission_ttl' => env('PERMISSION_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Role Permission Cache TTL
    |--------------------------------------------------------------------------
    |
    | TTL for role permission cache in seconds.
    |
    */

    'role_permission_ttl' => env('ROLE_PERMISSION_CACHE_TTL', 3600),
];
```

Set configuration database:

```php
// config/database.php
return [
    // ...

    'connections' => [
        // ...
        'pgsql' => [
            // ...
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true,
            ],
        ],

        'pgsql-direct' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST_DIRECT', '127.0.0.1'),
            'port' => env('DB_PORT_DIRECT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        // ...
    ],

    'redis' => [
        // ...

        // ✅ Tambahkan connection khusus untuk JWT
        'jwt' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_JWT_DB', '1'), // Database terpisah untuk JWT
        ],
    ],
];
```

Set configuration notification discord

```php
// config/services.php
return [
    // ...

    'discord' => [
        'webhook_url' => env('DISCORD_WEBHOOK_URL'),
    ],

    //* Application Services
    'application' => [
        'api_key' => env('API_SERVICE_KEY'),
        'control_center_service' => [
            'service_name' => env('CONTROL_CENTER_SERVICE_NAME', 'ControlCenterService'),
            'url' => env('CONTROL_CENTER_SERVICE_URL', 'http://control-center-service:8000/api/v1/control-center'),
            'timeout' => env('CONTROL_CENTER_SERVICE_TIMEOUT', 5),
            'retries' => env('CONTROL_CENTER_SERVICE_RETRIES', 3),
            'retry_delay' => env('CONTROL_CENTER_SERVICE_RETRY_DELAY', 200),
        ],
    ],
];
```

Setup your `.env` file and run the migrations:

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Migration & Seeding:

```bash
# Custom Command Migration/Seed
# Run migrations/seeds using pgsql-direct or tenant-specific connection
php artisan app:migrate --fresh --seed

# Refresh/Rollback a Specific Migration
php artisan migrate:refresh --path=/database/migrations/your_migration_file_name.php
php artisan migrate --path=/database/migrations/your_migration_file_name.php

# Custom Command Seed
# Run all seeds using pgsql-direct or tenant-specific connection
php artisan app:seed

# Run class UsersSeeder seeds using pgsql-direct or tenant-specific connection
php artisan app:seed --class=UsersSeeder

# Run by tenantId and class UsersSeeder seeds using pgsql-direct or tenant-specific connection
php artisan app:seed --tenant=33 --class=UsersSeeder

# Migration
php artisan migrate
# or migration with seed
php artisan migrate --seed

php artisan db:seed
```

Create Seeders Class from Existing Table (from package orangehill/iseed)

```bash
# Install Package
composer require orangehill/iseed --dev

# Single Table
php artisan iseed [nama_tabel]

# Multi Table
php artisan iseed [nama_table_01,nama_table_02,nama_table_03]

# Overidde Existing Seeders Class
php artisan iseed [nama_tabel] --force
```

Check Error Syntax (Lint Check):

```bash
php -l {directory}/{filename}.php
```

Running auth service mode development:

``` bash
php artisan serve --port=8001
```

View real-time logs
```bash
php artisan pail --timeout=0
tail -f storage/logs/laravel.log
```

Remove the cached bootstrap files and clear logs:

``` bash
# Laravel 12
php artisan optimize:clear && php artisan logs:clear

# Laravel 13
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear && php artisan logs:clear
```

## Running Docker

Running auth service mode development:

``` bash
# build & start
docker compose -f docker-compose.local.yml up --build -d

# show logs
docker logs -f auth-service

# stop & remove
docker compose -f docker-compose.local.yml down && docker image rm auth-service-multi-tenant:latest
```

Running auth service mode production:

``` bash
# build & start
docker compose -f docker-compose.prod.yml up --build -d

# stop & remove
docker compose -f docker-compose.prod.yml down && docker image rm auth-service-multi-tenant:latest
```

Enter bash container:

``` bash
docker exec -it auth-service bash
```

Permission denied in `storage/logs`:

Run this command on the host (or inside the container with root)

```bash
# command on the host
docker exec -it -u root auth-service chown -R 1000:1000 /var/www/storage /var/www/bootstrap/cache

# or command on the host
docker exec -it -u root auth-service chmod -R ug+rwX storage bootstrap/cache
```

Sync old data auth users:

```bash
clear && php artisan app:sync-old-auth-users-to-control-center

## Reprocess pending messages from table `outboxes`
clear && php artisan app:process-outbox
```

Running Worker Redis:

```bash
## Clear failed jobs
php artisan queue:flush

## Running jobs (auth internal sync & email)
docker exec -it auth-service php artisan queue:work redis --queue=auth_internal_sync,default,emails --verbose
```

Run K6 performance test

```bash
k6 run script.js
k6 run script.js > output.log
```

## TODO

