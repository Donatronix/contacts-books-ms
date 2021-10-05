<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Email;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ReferredContactsTableSeeder extends Seeder
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
            for ($x = 1; $x <= $faker->numberBetween(1, 5); $x++) {
                $row = new Email();
                $row->contact()->associate($contact);
                $row->save();
            }
        }
    }
}
