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
    public function run()
    {
        $faker = Faker::create('en_GB');

        //$codes = [205, 251, 256, 334, 907, 480, 520, 602, 623, 928, 479, 501, 870];

        $contacts = Contact::all();

        for ($x = 0; $x <= 20; $x++) {
            $row = ContactPhone::factory()->create();


            $row->contact()->associate($contacts->random());

//            $row->phone = '(' . $codes[rand(0, count($codes) - 1)] . ') ' . rand(100, 999) . '-'. rand(100, 999) . rand(1, 9);

            $row->type = $faker->randomElement([
                ContactPhone::TYPE_CELL,
                ContactPhone::TYPE_WORK,
                ContactPhone::TYPE_HOME,
                ContactPhone::TYPE_OTHER
            ]);

            $row->is_default = $faker->randomElement([]);
            $row->save();
        }
    }
}
