<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class InvitedReferralListener
{
    /**
     * Handle the event.
     *
     * @param $data
     */
    public function handle($data)
    {
        Log::info($data);
    }
}
