<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

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

        foreach ($contacts as $contact) {
            for ($i = 0; $i <= $faker->numberBetween(1, 5); $i++) {
                $data = Address::factory()->create([
                    'is_default' => $i === 0
                ]);
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
