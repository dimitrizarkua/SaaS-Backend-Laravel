<?php

namespace App\Console\Commands;

use App\Events\UserCreated;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Class CreateUserCommand
 *
 * @package App\Console
 */
class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
                            {--email= : Email}
                            {--password= : Password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new user';

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $email    = $this->option('email') ?: $this->ask('What email should we use for the account?');
        $password = $this->option('password') ?: $this->ask('What password should we use for the account?');

        $user           = new User();
        $user->email    = $email;
        $user->password = Hash::make($password);
        $user->saveOrFail();

        event(new UserCreated($user));

        $this->info('User account created successfully.');
    }
}
