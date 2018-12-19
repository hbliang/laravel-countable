<?php


namespace Hbliang\LaravelCountable\Traits;


use Hbliang\LaravelCountable\CountableFacade as Countable;
use Illuminate\Support\Facades\Redis;

trait CountableTrait
{
    public function addCount(string $column, int $value = 1)
    {
        if ($value === 0) {
            return null;
        }

        $redisKeyName = Countable::redisKeyName(get_class($this), (string)$this->getKey(), $column);

        return $value > 0 ? Redis::incrby($redisKeyName, $value) : Redis::decrby($redisKeyName, abs($value));
    }

    public abstract function getKey();
}