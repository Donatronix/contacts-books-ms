<?php

namespace App\Providers;

use App\Listeners\InvitedReferralListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'invitedReferral' => [
            InvitedReferralListener::class
        ]
    ];
}
