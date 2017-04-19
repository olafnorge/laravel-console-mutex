<?php

use Illuminate\Console\Command;
use olafnorge\Console\WithoutOverlapping;

class NullTimeoutCommand extends Command
{
    use WithoutOverlapping;

    protected $signature = 'icm:null-timeout-command';
    protected $mutexTimeout = null;

    public function handle()
    {
        $this->info('Done!');
    }
}
