<?php


namespace Hbliang\LaravelCountable;


use Hbliang\LaravelCountable\Contracts\CountableInterface;
use Hbliang\LaravelCountable\Exceptions\NotCountableException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Countable
{
    protected $redisKeyName;
    protected $targets;
    protected $modelInstances = [];

    public function __construct($config)
    {
        $this->redisKeyName = $config['redis_key'] ?? 'laravel-countable';
        $this->targets = $config['targets'] ?? [];
    }

    /**
     * commit to db
     * @param $modelName
     * @param $attribute
     */
    public function commit(string $modelName, string $attribute)
    {
        $table = $this->getTableNameByModelName($modelName);
        $model = $this->getModelByName($modelName);

        $cursor = 0;

        do {
            list($cursor, $keys) = Redis::scan($cursor, 'match', $this->redisKeyName($modelName, '*', $attribute));
            foreach ($keys as $key) {
                $primaryKey = $this->extraPrimaryKeyFromRedisKey($key);
                $value = intval(Redis::getset($key, 0));

                if ($value === 0) {
                    continue;
                }

                Log::info("commit {$value} into {$table}.{$primaryKey}.{$attribute}");
                $db = DB::table($table)->where($model->getKeyName(), $primaryKey);

                if ($value > 0) {
                    $db->increment($attribute, $value);
                } else {
                    $db->decrement($attribute, abs($value));
                }
            }

        } while ($cursor != 0);
    }

    public function run()
    {
        Log::info('start to run countable task.');

        foreach ($this->targets as $target) {
            // do not implement CountableInterface
            if (!in_array(CountableInterface::class, class_implements($target))) {
                throw new NotCountableException("please let {$target} implement CountableInterface}");
            }

            $attributes = call_user_func([$target, 'getCountables']);
            foreach ($attributes as $attribute) {
                $this->commit($target, $attribute);
            }
        }

        Log::info('countable task done.');
    }

    public function extraPrimaryKeyFromRedisKey(string $key): string
    {
        $fields = explode(':', $key);
        return $fields[2];
    }

    public function redisKeyName(string $model, string $primaryKey, string $attribute): string
    {
        $table = $this->getTableNameByModelName($model);
        return "{$this->redisKeyName}:{$table}:{$primaryKey}:{$attribute}";
    }

    public function getTableNameByModelName(string $name): string
    {
        return $this->getModelByName($name)->getTable();
    }

    /**
     * @param string $name
     * @return Model
     */
    public function getModelByName(string $name): Model
    {
        if (!isset($this->modelInstances[$name])) {
            $this->modelInstances[$name] = with(new $name);
        }

        return $this->modelInstances[$name];
    }
}