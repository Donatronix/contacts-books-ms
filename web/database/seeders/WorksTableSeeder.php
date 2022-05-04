<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Work;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class WorksTableSeeder extends Seeder
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
            for ($i = 0; $i <= $faker->numberBetween(1, 2); $i++) {
                $data = Work::factory()->create();
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
