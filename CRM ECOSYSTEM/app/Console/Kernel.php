<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('gen:retainers')->everyMinute();
        $schedule->command('webhooks:remove')->everyThirtyMinutes();
        $schedule->command('lead:unassign')->cron('0 4 * * *');
        $schedule->command('reset:password')->cron('0 3 */27 * *');
        $schedule->command('reset:chargebacks')->cron('0 3 */27 * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
