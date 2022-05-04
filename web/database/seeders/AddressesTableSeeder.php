<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class AddressesTableSeeder extends Seeder
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('en_GB');

        $contacts = Contact::all();

        foreach ($contacts as $contact) {
            for ($i = 0; $i <= $faker->numberBetween(1, 2); $i++) {
                $data = Address::factory()->create();
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
