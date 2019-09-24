<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Passport;

/**
 * Class SetupOAuthCommand
 *
 * @package App\Console
 */
class SetupOAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'oauth:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the commands necessary to prepare Passport for use';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = Passport::client();

        $clients = ['Steamatic SPA'];

        foreach ($clients as $clientName) {
            $existing = $client->where('name', $clientName)->first();
            if (!$existing) {
                $this->call('passport:client', ['--password' => true, '--name' => $clientName]);
            }
        }
    }
}
