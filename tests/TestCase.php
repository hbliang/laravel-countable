<?php


namespace Hbliang\LaravelCountable\Test;


use Hbliang\LaravelCountable\CountableFacade;
use Hbliang\LaravelCountable\CountableServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [CountableServiceProvider::class];
    }

    protected  function getPackageAliases($app)
    {
        return [
            'Countable' => CountableFacade::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ .'/database/migrations');
    }
}