<?php

namespace App\Listeners;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;
use Sumra\SDK\Facades\PubSub;

class GetOwnerByPhoneListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param array $event
     *
     * @return void
     */
    public function handle(array $data): void
    {
        try {
            if (sizeof($data)) {
                // select user that owns contact based the phone number
                $user = \DB::table('contacts')
                    ->join('phones', 'contacts.id', 'phones.contact_id')
                    ->where('phones.value', $data["chat_id"])
                    ->first();

                if(!$user){
                    // if contact belongs to no existing user, initiate conversation with a random user
                    $user = Contact::inRandomOrder()->limit(1)->first();
                }

                $data["bot_name"] = "{$user->first_name} {$user->last_name}";
                $data["bot_username"] = $user->user_id;
                $data["receiver"] = "{$user->first_name} {$user->last_name}";

                PubSub::publish('saveWhatsappUpdates', $data, config('pubsub.queue.communications'));
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
