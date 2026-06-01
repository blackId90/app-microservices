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

```bash
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
];
```

Set configuration database:

```php
// config/database.php
return [
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
];
```

Set configuration has access:

```php
// config/hasaccess.php
return [

    'exclude' => [

        /*
        |--------------------------------------------------------------------------
        | Exclude Route Name
        |--------------------------------------------------------------------------
        |
        | This value is the route name of your application's route, which will be excluded
        | from the HasAccess middleware.
        |
        */

        'route_name' => [
            // add another route name here
        ],

        /*
        |--------------------------------------------------------------------------
        | Exclude from Header X-Request-Source
        |--------------------------------------------------------------------------
        |
        | This value is the header X-Request-Source of your application's route, which will be excluded
        | from the HasAccess middleware.
        |
        */

        'header' => [
            'AuthService',
            // add another header here
        ],

    ],

    'action' => [

        /*
        |--------------------------------------------------------------------------
        | Valid Parameter List Data (Browse)
        |--------------------------------------------------------------------------
        |
        | 1 = Without Deleted.
        | 2 = Deleted Data.
        | 3 = All Data.
        |
        */

        'browse' => [
            'type' => 'type_list',
			'value' => ['1', '2', '3']
        ],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Create Data (Create)
        |--------------------------------------------------------------------------
        |
        | 1 = Default Value.
        |
        */

        'create' => [
			'type' => 'type_created',
			'value' => ['1']
		],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Read Data (Read)
        |--------------------------------------------------------------------------
        |
        | 1 = Without Deleted.
        | 2 = All Data.
        |
        */

		'read' => [
			'type' => 'type_detail',
			'value' => ['1', '2']
		],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Update Data (Edit)
        |--------------------------------------------------------------------------
        |
        | 1 = Without Deleted.
        | 2 = All Data.
        |
        */

		'update' => [
			'type' => 'type_updated',
			'value' => ['1', '2']
		],

        /*
        |--------------------------------------------------------------------------
        | Value Parameter Delete Data (Delete)
        |--------------------------------------------------------------------------
        |
        | 1 = Soft Deleted.
        | 2 = Restore from Trash.
        | 3 = Deleted from Trash.
        | 4 = Permanent Deleted.
        |
        */

		'delete' => [
			'type' => 'type_deleted',
			'value' => ['1', '2', '3', '4']
		],

    ]

];
```

Set configuration jwt token:

```php
// config/jwt.php
<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Secret
    |--------------------------------------------------------------------------
    |
    | Don't forget to set this in your .env file, as it will be used to sign
    | your tokens. A helper command is provided for this:
    | `php artisan jwt:secret`
    |
    | Note: This will be used for Symmetric algorithms only (HMAC),
    | since RSA and ECDSA use a private/public key combo (See below).
    |
    */

    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Authentication Keys
    |--------------------------------------------------------------------------
    |
    | The algorithm you are using, will determine whether your tokens are
    | signed with a random string (defined in `JWT_SECRET`) or using the
    | following public & private keys.
    |
    | Symmetric Algorithms:
    | HS256, HS384 & HS512 will use `JWT_SECRET`.
    |
    | Asymmetric Algorithms:
    | RS256, RS384 & RS512 / ES256, ES384 & ES512 will use the keys below.
    |
    */

    'algo' => env('JWT_ALGO'),

    'keys' => [

        /*
        |--------------------------------------------------------------------------
        | Public Key
        |--------------------------------------------------------------------------
        |
        | A path or resource to your public key.
        |
        | E.g. 'file://path/to/public/key'
        |
        */

        'public' => env('JWT_PUBLIC_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Private Key
        |--------------------------------------------------------------------------
        |
        | A path or resource to your private key.
        |
        | E.g. 'file://path/to/private/key'
        |
        */

        'private' => env('JWT_PRIVATE_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Passphrase
        |--------------------------------------------------------------------------
        |
        | The passphrase for your private key. Can be null if none set.
        |
        */

        'passphrase' => env('JWT_PASSPHRASE'),

    ],

    /*
    |--------------------------------------------------------------------------
    | JWT time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token will be valid for.
    | Defaults to 1 hour.
    |
    | You can also set this to null, to yield a never expiring token.
    | Some people may want this behaviour for e.g. a mobile app.
    | This is not particularly recommended, so make sure you have appropriate
    | systems in place to revoke the token if necessary.
    | Notice: If you set this to null you should remove 'exp' element from 'required_claims' list.
    |
    */

    'ttl' => (int) env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Refresh time to live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token can be refreshed
    | within. I.E. The user can refresh their token within a 2 week window of
    | the original token being created until they must re-authenticate.
    | Defaults to 2 weeks.
    |
    | You can also set this to null, to yield an infinite refresh time.
    | Some may want this instead of never expiring tokens for e.g. a mobile app.
    | This is not particularly recommended, so make sure you have appropriate
    | systems in place to revoke the token if necessary.
    |
    */

    'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 20160),

    /*
    |--------------------------------------------------------------------------
    | Required Claims
    |--------------------------------------------------------------------------
    |
    | Specify the required claims that must exist in any token.
    | A TokenInvalidException will be thrown if any of these claims are not
    | present in the payload.
    |
    */

    'required_claims' => [
        'iss',
        'iat',
        'exp',
        'nbf',
        'sub',
        'jti',
    ],

    /*
    |--------------------------------------------------------------------------
    | JWT issuer (iss claim)
    |--------------------------------------------------------------------------
    |
    | Unique string (often a URL) identifying the party—such as an
    | identity provider or service—that created and signed a JSON Web Token.
    | It verifies token authenticity, prevents misuse, and confirms the source,
    | typically using the https scheme for identification.
    |
    */

    'issuer_claims' => env('JWT_ISSUER'),

    /*
    |--------------------------------------------------------------------------
    | Persistent Claims
    |--------------------------------------------------------------------------
    |
    | Specify the claim keys to be persisted when refreshing a token.
    | `sub` and `iat` will automatically be persisted, in
    | addition to the these claims.
    |
    | Note: If a claim does not exist then it will be ignored.
    |
    */

    'persistent_claims' => [
        // 'foo',
        // 'bar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lock Subject
    |--------------------------------------------------------------------------
    |
    | This will determine whether a `prv` claim is automatically added to
    | the token. The purpose of this is to ensure that if you have multiple
    | authentication models e.g. `App\User` & `App\OtherPerson`, then we
    | should prevent one authentication request from impersonating another,
    | if 2 tokens happen to have the same id across the 2 different models.
    |
    | Under specific circumstances, you may want to disable this behaviour
    | e.g. if you only have one authentication model, then you would save
    | a little on token size.
    |
    */

    'lock_subject' => true,

    /*
    |--------------------------------------------------------------------------
    | Leeway
    |--------------------------------------------------------------------------
    |
    | This property gives the jwt timestamp claims some "leeway".
    | Meaning that if you have any unavoidable slight clock skew on
    | any of your servers then this will afford you some level of cushioning.
    |
    | This applies to the claims `iat`, `nbf` and `exp`.
    |
    | Specify in seconds - only if you know you need it.
    |
    */

    'leeway' => (int) env('JWT_LEEWAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Blacklist Enabled
    |--------------------------------------------------------------------------
    |
    | In order to invalidate tokens, you must have the blacklist enabled.
    | If you do not want or need this functionality, then set this to false.
    |
    */

    'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),

    /*
    | -------------------------------------------------------------------------
    | Blacklist Grace Period
    | -------------------------------------------------------------------------
    |
    | When multiple concurrent requests are made with the same JWT,
    | it is possible that some of them fail, due to token regeneration
    | on every request.
    |
    | Set grace period in seconds to prevent parallel request failure.
    |
    */

    'blacklist_grace_period' => (int) env('JWT_BLACKLIST_GRACE_PERIOD', 0),

    /*
    |--------------------------------------------------------------------------
    | Cookies encryption
    |--------------------------------------------------------------------------
    |
    | By default Laravel encrypt cookies for security reason.
    | If you decide to not decrypt cookies, you will have to configure Laravel
    | to not encrypt your cookie token by adding its name into the $except
    | array available in the middleware "EncryptCookies" provided by Laravel.
    | see https://laravel.com/docs/master/responses#cookies-and-encryption
    | for details.
    |
    | Set it to true if you want to decrypt cookies.
    |
    */

    'decrypt_cookies' => false,

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
];
```

Set connection database:

```php
// config/database.php
return [
    'connections' => [
        // ...
        'pgsql' => [
            // ...
            //* EITHER associative option style (recommended):
            'options' => [
                PDO::ATTR_EMULATE_PREPARES => true, // if true value boolean 0/1 and false value boolean true/false
            ],
            //* OR numeric option style (also supported):
            // 'options' => [
            //     PDO::ATTR_EMULATE_PREPARES,
            // ],
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
    ]
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

# Default Laravel 12
php artisan migrate
# or
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

Check Error Syntax:

```bash
php -l [filename]
```

Running auth service mode development:

```bash
php artisan serve --port=8000
```

View real-time logs

```bash
php artisan pail --timeout=0
tail -f storage/logs/laravel.log
```

Remove the cached bootstrap files and clear logs:

```bash
# Laravel 12
php artisan optimize:clear && php artisan logs:clear

# Laravel 13
php artisan config:clear && php artisan route:clear && php artisan view:clear && php artisan cache:clear && php artisan logs:clear
```

## Running Docker

Running auth service mode development:

```bash
# build & start
docker compose -f docker-compose.local.yml up --build -d

# show logs
docker logs -f control-center-service

# stop & remove
docker compose -f docker-compose.local.yml down && docker image rm control-center-service-multi-tenant:latest
```

Running auth service mode production:

```bash
# build & start
docker compose -f docker-compose.prod.yml up --build -d

# stop & remove
docker compose -f docker-compose.prod.yml down && docker image rm control-center-service-multi-tenant:latest
```

Enter bash container:

```bash
docker exec -it control-center-service bash
```

Permission denied in `storage/logs`:

Run this command on the host (or inside the container with root)

```bash
# command on the host
docker exec -it -u root control-center-service chown -R 1000:1000 /var/www/storage /var/www/bootstrap/cache

# or command on the host
docker exec -it -u root control-center-service chmod -R ug+rwX storage bootstrap/cache
```

Running Worker Redis

```bash
# Clear failed jobs
clear && php artisan queue:flush

# Running jobs
clear && docker exec -it control-center-service php artisan queue:work redis --queue=sync_auth_queue,default --verbose
```

Run K6 performance test

```bash
k6 run k6-perfomance.js
k6 run k6-perfomance.js > output.log
```

## TODO
