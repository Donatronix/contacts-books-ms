<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            GroupsTableSeeder::class,
            ContactsTableSeeder::class,
            ContactEmailsTableSeeder::class,
            ContactPhonesTableSeeder::class,
            ContactsToGroupsTableSeeder::class,
            CategoriesTableSeeder::class,
            WorkSeeder::class,
            AddressSeeder::class,
            SiteSeeder::class,
            ChatSeeder::class,
            RelationSeeder::class,
        ]);
    }
}
