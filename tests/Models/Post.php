<?php


namespace Hbliang\LaravelCountable\Test\Models;


use Hbliang\LaravelCountable\Contracts\CountableInterface;
use Hbliang\LaravelCountable\Traits\CountableTrait;
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements CountableInterface
{
    use CountableTrait;

    public static function getCountables(): array
    {
        return ['views'];
    }
}