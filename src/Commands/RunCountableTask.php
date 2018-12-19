<?php


namespace Hbliang\LaravelCountable\Commands;


use Hbliang\LaravelCountable\Countable;
use Illuminate\Console\Command;

class RunCountableTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'countable:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run countable task';

    /**
     * @var Countable
     */
    protected $countable;


    public function __construct(Countable $countable)
    {
        parent::__construct();
        $this->countable = $countable;
    }

    /**
     * Execute the console command
     *
     * @return mixed
     */
    public function handle()
    {
        $this->countable->run();
    }
}