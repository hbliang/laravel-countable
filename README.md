# Larave Countable


This is a laravel pacakge to count something based on redis, in order to reduce the pressure of databse. For example, a Post entity has "views" field, a number indicating how many people look through it. `DB::table('posts')->increment('views')` is the easiest way to increase it. However, while you increase it in this way, it will be likely to face a speed problem that the performance of writes on the dababase server get reduced as site traffic grows. So I use redis as primary storage for counter as well as periodically retrive data from redis and save it into your main database such as mysql.

## Requirement
- `PHP^7.1`

## Install
`composer require hbliang/laravel-countable`

## Usage

First, you should publish configuration file with the following Artisan command:
`php artisan vendor:publish --provider="Hbliang/LaravelCountable/CountableServiceProvider"`

Second, let's update your model which has a field to get increased. Task a User model including a `views` field as example. You need to implements `Hbliang\LaravelCountable\Contracts\CountableInterface` contract on your User model, which requires that implement the a methods `getCountables()`. Don't forget to add `Hbliang\LaravelCountable\Traits\CountableTrait` to your Model.
The snippet of codes below is a example. 
```PHP
<?php

namespace App;

use Hbliang\LaravelCountable\Contracts\CountableInterface;
use Hbliang\LaravelCountable\Traits\CountableTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements CountableInterface
{
    use Notifiable, CountableTrait;

    public static function getCountables(): array
    {
        return ['views'];
    }
}

```

Third, you have to add your model to `target` array inside `config/countable.php`. Optionally, you can change prefix key name for redis.
For example:
```
<?php

return [
    'redis_key' => 'laravel-countable', // key for redis

    'targets' => [
        App\User::class,
    ],
];
```

Last, please add Cron entries below to you server, if you have not added it.:
`* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`

Right now, after following steps abov, you are able to completely run laravel with this package.


## PS
- For now, only support a single redis server instead a cluster of redis

