<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Contact;
use App\Models\Site;

class SiteSeeder extends Seeder
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

        foreach($contacts as $contact)
        {
            $is_default = true;
            for ($i=0; $i <= $faker->numberBetween(1, 5); $i++) {
                if ($i > 0) {
                    $is_default = false;
                }

                $data = Site::factory()->create();
                $data->is_default = $is_default;
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
