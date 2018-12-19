<?php


namespace Hbliang\LaravelCountable\Test;


use Hbliang\LaravelCountable\CountableFacade;
use Hbliang\LaravelCountable\Traits\CountableTrait;
use Illuminate\Support\Facades\Redis;

class CountableTraitTest extends TestCase
{
    public function testAddCountIncrby()
    {
        $mock = $this->getMockForTrait(CountableTrait::class);


        $primaryKey = 1;
        $column = 'views';
        $value = 10;
        $model = get_class($mock);

        $mock->expects($this->once())
            ->method('getKey')
            ->willReturn($primaryKey);

        $redisKeyName = 'test';



        CountableFacade::shouldReceive('redisKeyName')
            ->once()
            ->with($model, $primaryKey, $column)
            ->andReturn($redisKeyName);

        Redis::shouldReceive('decrby')
            ->never();
        Redis::shouldReceive('incrby')
            ->once()
            ->with($redisKeyName, $value)
            ->andReturn($value);

        $this->assertEquals($value, $mock->addCount($column, $value));

    }

    public function testAddCountDecrby()
    {
        $mock = $this->getMockForTrait(CountableTrait::class);

        $primaryKey = 1;
        $column = 'views';
        $value = -10;
        $model = get_class($mock);

        $mock->expects($this->once())
            ->method('getKey')
            ->willReturn($primaryKey);

        $redisKeyName = 'test';

        CountableFacade::shouldReceive('redisKeyName')
            ->once()
            ->with($model, $primaryKey, $column)
            ->andReturn($redisKeyName);

        Redis::shouldReceive('incrby')
            ->never();
        Redis::shouldReceive('decrby')
            ->once()
            ->with($redisKeyName, abs($value))
            ->andReturn($value);


        $this->assertEquals($value, $mock->addCount($column, $value));
    }

    public function testAddCountZero()
    {
        $mock = $this->getMockForTrait(CountableTrait::class);


        $mock->expects($this->never())->method('getKey');


        CountableFacade::shouldReceive('redisKeyName')
            ->never();

        Redis::shouldReceive('incrby')
            ->never();
        Redis::shouldReceive('decrby')
            ->never();

        $this->assertNull($mock->addCount('test', 0));
    }
}