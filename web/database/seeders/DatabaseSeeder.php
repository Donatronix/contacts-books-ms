<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
            CategoriesTableSeeder::class,
            GroupsTableSeeder::class,
            ContactsTableSeeder::class,
            ContactsToGroupsTableSeeder::class,
            EmailsTableSeeder::class,
            PhonesTableSeeder::class,
            WorkSeeder::class,
            AddressSeeder::class,
            SiteSeeder::class,
            ChatSeeder::class,
            RelationSeeder::class,
        ]);
    }
}
