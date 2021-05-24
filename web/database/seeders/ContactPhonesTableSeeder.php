<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\ContactPhone;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class ContactPhonesTableSeeder extends Seeder
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

        foreach($contacts as $contact) {
            $is_default = true;

            for ($x = 0; $x <= $faker->numberBetween(1, 5); $x++) {
                if ($x > 0) {
                    $is_default = false;
                }

                $row = ContactPhone::factory()->create();
                $row->is_default = $is_default;
                $row->contact()->associate($contact);
                $row->save();
            }
        }
    }
}
