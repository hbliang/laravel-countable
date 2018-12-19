<?php
namespace Hbliang\LaravelCountable;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CountableServiceProvider  extends  ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php',
            'countable'
        );

        $this->app->singleton('countable', function($app) {
            return new Countable(config('countable'));
        });

        $this->app->alias('countable', Countable::class);
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('countable.php'),
            ], 'config');

            $this->commands([
                \Hbliang\LaravelCountable\Commands\RunCountableTask::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command('countable:run')->everyMinute();
            });
        }
    }
}