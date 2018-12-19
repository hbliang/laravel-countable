<?php


namespace Hbliang\LaravelCountable;


use Illuminate\Support\Facades\Facade;

class CountableFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'countable';
    }
}