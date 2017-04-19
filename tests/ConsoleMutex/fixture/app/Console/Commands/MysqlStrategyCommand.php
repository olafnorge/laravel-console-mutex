<?php

use Illuminate\Console\Command;
use olafnorge\Console\WithoutOverlapping;

class MysqlStrategyCommand extends Command
{
    use WithoutOverlapping;

    protected $signature = 'icm:mysql-strategy-command';
    protected $mutexStrategy = 'mysql';

    public function handle()
    {
        $this->info('Done!');
    }
}
