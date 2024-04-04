# laravel-jwt-redis

[![](https://img.shields.io/packagist/v/chantouch/laravel-jwt-redis)](https://packagist.org/packages/chantouch/laravel-jwt-redis)
[![](https://github.styleci.io/repos/234304505/shield?branch=master)](https://github.styleci.io/repos/234304505)
[![](https://img.shields.io/packagist/l/chantouch/laravel-jwt-redis)](https://raw.githubusercontent.com/chantouch/laravel-jwt-redis/master/LICENSE)
[![](https://img.shields.io/packagist/dt/chantouch/laravel-jwt-redis?color=success)](https://packagist.org/packages/chantouch/laravel-jwt-redis)

> This package allows JWT-authenticated users to be stored and management in Redis with
their roles, permissions, statuses and anything you want.

> This package is originally forked from [sametsahindogan/laravel-jwtredis](https://github.com/sametsahindogan/laravel-jwtredis)

<a><img src="https://cdn.auth0.com/blog/jwtalgos/logo.png" width="80"></a>
<a><img src="https://camo.githubusercontent.com/4d28958330457f539357f95c739cf5763e327c0720ac3dad71f255e65fbbd41b/68747470733a2f2f63646e342e69636f6e66696e6465722e636f6d2f646174612f69636f6e732f72656469732d322f313435312f556e7469746c65642d322d3531322e706e67" width="80"></a>

>Also, this package has an observer for listening and updating  to your user model 
on Redis. This observer is triggered `when you assign roles & permissions to user, or update
and delete to your user` model.

## Requirements

This package work with together [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) and [spatie/laravel-permission](https://github.com/spatie/laravel-permission) package under the hood.

![#](https://placehold.it/15/f03c15/000000?text=+) `Make sure to install and configure these dependencies. You must publish, migrate etc. all packages.` ![#](https://placehold.it/15/f03c15/000000?text=+)
 
- [nrk/predis](https://github.com/nrk/predis) **>= 1.1** (**Recommended 1.1**)
- [tymon/jwt-auth](https://github.com/tymondesigns/jwt-auth) **>= 1.4** (**Recommended 1.4.x**)
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission) **>= 5.5** (**Recommended 5.5**) (Optional)

## Installation

```bash
composer require chantouch/laravel-jwt-redis
```
Once this has finished, you will need to add&change these values in `.env` file:
```dotenv
CACHE_DRIVER=redis
REDIS_CLIENT=predis
```
Next, you will need to change the `guards` and `providers` arrays in your `config/auth.php` config as follows:
```php
<?php
return [
'guards' => [
        'api' => [
            'driver' => 'jwt_redis',
            'provider' => 'users'
        ],
    ],

'providers' => [
        'users' => [
            'driver' => 'jwt_redis_user',
            'model' =>  App\Models\User::class, /* Your User Model */
        ],
    ],
];
```
This package uses auto-discovery to register the service provider but if you'd rather do it manually, the service provider is: add to `providers` array in your `config/app.php` config as follows:
```php
Chantouch\JWTRedis\JWTRedisServiceProvider::class,
```
You will want to publish the config using the following command:
```bash
php artisan vendor:publish --provider='Chantouch\JWTRedis\JWTRedisServiceProvider'
```

## Configurations

When everything is done, don't forget to add this Trait to your user model, if you are going to use with [spatie/laravel-permission](https://github.com/spatie/laravel-permission).
```php
use JWTRedisHasRoles;
```

When everything is done, don't forget to add this Trait to your user model, if you are not use with `laravel-permission`.
```php
use JWTRedis;
```
You need to add `$routeMiddleware` array in `app/Http/Kernel.php`
```php
<?php
return [
    'auth'               => \Chantouch\JWTRedis\Http\Middleware\Authenticate::class,
    'refreshable'        => \Chantouch\JWTRedis\Http\Middleware\Refreshable::class,
    'role'               => \Chantouch\JWTRedis\Http\Middleware\RoleMiddleware::class, // Optional
    'permission'         => \Chantouch\JWTRedis\Http\Middleware\PermissionMiddleware::class, // Optional
    'role_or_permission' => \Chantouch\JWTRedis\Http\Middleware\RoleOrPermissionMiddleware::class, // Optional
];
```

## Usage

 You do not have any instructions for use. This package only affects the background, functions in an almost identical way to Laravel session authentication, with a few exceptions. `All you have to do is change your middleware.(I mention this below)` You can use Laravel's Auth facade,
Tymon's JWTAuth facade and all [spatie/laravel-permission](https://github.com/spatie/laravel-permission) package methods as usual.<br>

* For user authentication by token; <br>
( Use this middleware if the user's identity is not important. This middleware only checks if Token is valid. Doesn't send to any query to any database.)
```php
Route::get("/example", "ExampleController@example")->middleware('auth');
```
* To check user authorization, you need to this one of these middlewares;<br>
( Use this middleware if the user's identity is important. This middlewares fetch user from Redis and mark as authorized 
to Laravel's Request object. And you will reach all default Auth facade's methods you want. Just call Laravel's Auth facades.)
```php
Route::get("/example", "ExampleController@example")->middleware('role:admin|user');
Route::get("/example", "ExampleController@example")->middleware('permissions:get-user|set-user');
Route::get("/example", "ExampleController@example")->middleware('role_or_permission:admin|get-user');
```
* To refresh the token, you can add the `refreshable` middleware to the required route. You don't need to take any action on the controller of this route;<br>
( Also this middleware can refresh user from Redis if necessary. )
```php
Route::get("/example", "ExampleController@example")->middleware('refreshable');
```
<br>

`If you want to do different things, you can override those mentioned middlewares.`

**After using it as above, every authorization you made in your 
application, such as `Auth::user()` or `$user->can('permission')`, is always checked from Redis, not from the database.**

## Options

You can customize some options in that package. Check `config/jwt-redis.php` file.

* User Model
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Your User Model
    |--------------------------------------------------------------------------
    |
    | You can set specific user model.
    |
    */
    'user_model' => \App\Models\User::class,
];
```
* Observer
```php
<?php
return [
     /*
     |--------------------------------------------------------------------------
     | JWTRedis User Model Observer
     |--------------------------------------------------------------------------
     |
     | This observer class, listening all events on your user model. Is triggered
     | when you assign roles & permissions to user, or update and delete to
     | your user model.
     |
     */
    'observer' => Chantouch\JWTRedis\Observers\UserRedisObserver::class,
];
```
* Events Queue
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Observer Events Are Queued
    |--------------------------------------------------------------------------
    |
    | If this option is true, model's events are processed as a job on queue.
    |
    | * ~ Don't forget to run Queue Worker if this option is true. ~ *
    |
    */
    'observer_events_queue' => true,
];
```
* Cache Time
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Cache on Redis up to jwt_ttl value.
    |--------------------------------------------------------------------------
    |
    | If it's option is true, user stored in Redis up to jwt_ttl value time.
    |
    */
    'redis_ttl_jwt' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache on Redis up to specific time
    |--------------------------------------------------------------------------
    |
    | If you don't want to store user in Redis until JWT expire time, 
    | you can set this value as minute.
    |
    */
    'redis_ttl' => 60,
];
```
* Cache Prefix
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | If it's user id is 1, this user stored in Redis as auth_1.
    |
    */
    'redis_auth_prefix' => 'auth_',
];
```
* Banned User Check
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Banned User Checking
    |--------------------------------------------------------------------------
    |
    | If the check_banned_user option is true, that users cannot access
    | the application.
    |
    */
    'check_banned_user' => false,

    /*
    |--------------------------------------------------------------------------
    | Status Column For Banned User Checking
    |--------------------------------------------------------------------------
    |
    | You can set your specific column name of your user model.
    |
    */
    'status_column_title' => 'status',


    /*
    |--------------------------------------------------------------------------
    | Restricted statuses For Banned User Checking
    |--------------------------------------------------------------------------
    |
    | If the user has one of these statuses and trying to reach your application,
    | JWTRedis throws AccountBlockedException.
    | You can set the message (check it errors array) that will return in this
    | exception.
    |
    */
    'banned_statuses' => [
        'banned',
        'deactivate'
    ],
];
```
* Relation Caching
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Cache This Relations When User Has Authenticated
    |--------------------------------------------------------------------------
    |
    | You can add this array to your own relations, anything you want to store
    | in Redis. We recommend caching only roles and permissions here as much as
    | possible.
    |
    */
    'cache_relations' => [
        'roles.permissions',
        'permissions'
    ],
];
```
* Customize Exceptions
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Customize All Exception Messages and Codes
    |--------------------------------------------------------------------------
    |
    | You can customize error code,message,title for your application.
    |
    */
    'errors' => [
       'TokenNotProvidedException' => [
           'title' => 'Your custom title',
           'message' => 'Your custom error message.',
           'code' => 99999
       ]
    ],
];
```

## Example Project

Here is an [example](https://github.com/chantouch/laravel-jwt-redis-example) using laravel-jwt-redis. You can examine in detail.

## Performance Improvements Tips
This package requirement the predis package by default.

You may install the PhpRedis PHP extension via PECL. The extension is more complex to install but may yield better performance for applications that make heavy use of Redis. Predis is the alternative for PhpRedis on pure PHP and does not require any additional C extension by default.

"PhpRedis is faster about x6 times. Using igbinary serializer reduces stored data size about 3x times. If Redis installed on separate machines, reducing network traffic is a very significant speedup."

In my opinion, using [PhpRedis](https://github.com/phpredis/phpredis) and serializer as igbinary ([Lodash](https://github.com/akalongman/laravel-lodash) package it provide this for Laravel.) in production environment gives a great performance.

You can review this  [article](https://medium.com/@akalongman/phpredis-vs-predis-comparison-on-real-production-data-a819b48cbadb) for performance comparison [PhpRedis](https://github.com/phpredis/phpredis) vs. [Predis](https://github.com/nrk/predis).

## License
MIT © [Sek Chantouch](https://github.com/chantouch/laravel-jwt-redis/blob/master/LICENSE)
