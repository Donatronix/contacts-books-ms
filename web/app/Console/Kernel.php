<?php

namespace App\Console;

use App\Console\Commands\KeyGenerateCommand;
use App\Console\Commands\RouteListCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        KeyGenerateCommand::class,
        RouteListCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        //
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return string
     */
    /**
     * @return string
     */
    protected function scheduleTimezone(): string
    {
        return 'Europe/London';
    }
}
