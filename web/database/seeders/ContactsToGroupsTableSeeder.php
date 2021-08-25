<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Group;
use Illuminate\Database\Seeder;

class ContactsToGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $groups = Group::all();

        foreach ($groups as $group) {
            $contacts = Contact::all()->random(5);

            $group->contacts()->attach($contacts);
        }
    }
}
