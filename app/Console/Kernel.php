<?php

namespace App\Console;

use App\Jobs\Contacts\ChangeStatusOfContactsWhichWereNotActiveForDays;
use App\Jobs\Finance\LockFinancialEntity;
use App\Jobs\Jobs\UnsnoozeJobsWhichSnoozeDateIsInThePast;
use App\Jobs\Jobs\UnsnoozeJobTasksWhichSnoozeDateIsInThePast;
use App\Jobs\RecurringJobs\ProcessRecurringJobs;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Class Kernel
 *
 * @package App\Console
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new ChangeStatusOfContactsWhichWereNotActiveForDays())
            ->daily();
        $schedule->job(new ProcessRecurringJobs(), 'jobs')
            ->daily()
            ->timezone('Australia/Sydney');
        $schedule->job(new LockFinancialEntity())
            ->dailyAt('00:30')
            ->timezone('Australia/Sydney');

        $schedule->job(new UnsnoozeJobsWhichSnoozeDateIsInThePast(), 'jobs')
            ->everyFiveMinutes();
        $schedule->job(new UnsnoozeJobTasksWhichSnoozeDateIsInThePast(), 'jobs')
            ->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');

        //This is a file containing console commands for local development.
        $localCommandsFiles = base_path('routes/console_local.php');
        if (file_exists($localCommandsFiles)) {
            require $localCommandsFiles;
        }
    }
}
