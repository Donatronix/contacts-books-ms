<?php

namespace App\Listeners;

class InvitedReferralListener
{
    /**
     * Handle the event.
     *
     * @param $data
     */
    public function handle($data)
    {
        // Send result by pubsub
        \PubSub::transaction(function () {})->publish('InvitedReferralResponse', $data, 'referrals');
    }
}
