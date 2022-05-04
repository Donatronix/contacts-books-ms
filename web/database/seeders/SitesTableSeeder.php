<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Site;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class SitesTableSeeder extends Seeder
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

        foreach ($contacts as $contact) {
            for ($i = 0; $i <= $faker->numberBetween(1, 3); $i++) {
                $data = Site::factory()->create();
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
