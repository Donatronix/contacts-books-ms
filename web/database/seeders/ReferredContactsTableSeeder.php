<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactEmail;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ReferredContactsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_GB');

        $contacts = Contact::all();

        foreach($contacts as $client) {
            $is_default = false;

            for ($x = 1; $x <= $faker->numberBetween(1, 5); $x++) {
                if (!$is_default) {
                    $is_default = $faker->boolean;
                }

                $row = new ContactEmail();
                $row->contact()->associate($client);

                $row->save();
            }
        }
    }
}
