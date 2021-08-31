<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Work;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
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
            for ($i = 0; $i <= $faker->numberBetween(1, 5); $i++) {
                $data = Work::factory()->create([
                    'is_default' => $i === 0
                ]);
                $data->contact()->associate($contact);
                $data->save();
            }
        }
    }
}
