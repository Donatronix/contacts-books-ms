<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Phone;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class PhonesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $faker = Faker::create('en_GB');

        $contacts = Contact::all();

        foreach ($contacts as $contact) {
            for ($x = 0; $x <= $faker->numberBetween(1, 5); $x++) {
                $row = Phone::factory()->create([
                    'is_default' => $x === 0
                ]);
                $row->contact()->associate($contact);
                $row->save();
            }
        }
    }
}
