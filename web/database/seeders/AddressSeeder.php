<?php

namespace Database\Seeders;

use App\Models\Contact;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use App\Models\Address;

class AddressSeeder extends Seeder
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

        foreach($contacts as $contact)
        {
            $is_default = true;
            for ($i=0; $i <= $faker->numberBetween(1, 5); $i++)
            {
                if ($i > 0) {
                    $is_default = false;
                }

                $data = Address::factory()->create();
                $data->is_default = $is_default;
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
