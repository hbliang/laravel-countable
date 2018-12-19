<?php

namespace Hbliang\LaravelCountable\Test;

use Hbliang\LaravelCountable\Countable;
use Hbliang\LaravelCountable\CountableFacade;
use Hbliang\LaravelCountable\Exceptions\NotCountableException;
use Hbliang\LaravelCountable\Test\Models\Post;
use Hbliang\LaravelCountable\Test\Models\PostWithoutImplementCountable;
use Illuminate\Support\Facades\Redis;

class CountableTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('countable.redis_name', 'countable');
    }

    public function testCommit()
    {
        $post = new Post();
        $post->save();

        $redisKeyName = CountableFacade::redisKeyName(Post::class, 1, 'views');
        Redis::shouldReceive('scan')
            ->once()
            ->andReturn([
                0,
                [$redisKeyName]
            ]);

        Redis::shouldReceive('getset')
            ->once()
            ->with($redisKeyName, 0)
            ->andReturn(10);

        $mock = $this->getMockBuilder(Countable::class)
            ->enableOriginalConstructor()
            ->setMethods(['getTableNameByModelName', 'extraPrimaryKeyFromRedisKey', 'redisKeyName'])
            ->setConstructorArgs([['redis_name' => 'countable']])
            ->getMock();

        $mock->expects($this->once())
            ->method('getTableNameByModelName')
            ->willReturn('posts');

        $mock->expects($this->once())
            ->method('redisKeyName')
            ->willReturn('countable:posts:*:views');

        $mock->expects($this->once())
            ->method('extraPrimaryKeyFromRedisKey')
            ->willReturn(1);

        $mock->commit(Post::class, 'views');

        $post->refresh();

        $this->assertEquals(10, $post->views);
    }

    public function testRun()
    {
        $mock = $this->getMockBuilder(Countable::class)
            ->enableOriginalConstructor()
            ->setMethods(['commit'])
            ->setConstructorArgs([['targets' => [Post::class]]])
            ->getMock();


        $mock->expects($this->once())
            ->method('commit');

        $mock->run();
    }

    public function testRunWillThrowException()
    {
        $countable = new Countable(['targets' => [PostWithoutImplementCountable::class]]);

        $this->expectException(NotCountableException::class);

        $countable->run();
    }

    public function testExtraPrimaryKeyFromRedisKey()
    {
        $key = '{$this->redisKeyName}:{$table}:{$primaryKey}:{$attribute}';
        $countable = new Countable([]);
        $this->assertEquals('{$primaryKey}', $countable->extraPrimaryKeyFromRedisKey($key));
    }

    public function testRedisKeyName()
    {
        $config = ['redis_key' => 'redis'];

        $mock = $this->getMockBuilder(Countable::class)
            ->enableOriginalConstructor()
            ->setMethods(['getTableNameByModelName'])
            ->setConstructorArgs([$config])
            ->getMock();

        $mock->expects($this->once())
            ->method('getTableNameByModelName')
            ->willReturn('models');

        $this->assertEquals('redis:models:1:attribute', $mock->redisKeyName('model', 1, 'attribute'));
    }

    public function testGetTableNameByModelName()
    {
        $countable = new Countable([]);
        $this->assertEquals('posts', $countable->getTableNameByModelName(Post::class));
    }

    public function testGetModelByName()
    {
        $countable = new Countable([]);
        $this->assertInstanceOf(Post::class, $countable->getModelByName(Post::class));
    }
}
