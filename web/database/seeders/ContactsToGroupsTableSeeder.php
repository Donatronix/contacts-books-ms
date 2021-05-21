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
        $list = Group::all();

        foreach ($list as $group){
            $contacts = Contact::all()->random(10);

            $group->contacts()->attach($contacts);
        }
    }
}
