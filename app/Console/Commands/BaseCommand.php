<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Class BaseCommand
 *
 * @package App\Console\Commands
 */
class BaseCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected $hidden = true;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hidden_base_command';

    /**
     * Aborts script execution
     *
     * @param null|string $message
     */
    protected function abort(?string $message)
    {
        $this->error($message);
        exit();
    }
}
