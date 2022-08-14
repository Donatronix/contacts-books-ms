<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Seeds for all
        $this->call([
            CategoriesTableSeeder::class,
            GroupsTableSeeder::class,
            ContactsTableSeeder::class,
            ContactsToGroupsTableSeeder::class,
            PhonesTableSeeder::class,
            EmailsTableSeeder::class,
            ChatsTableSeeder::class,
            AddressesTableSeeder::class,
            WorksTableSeeder::class,
            SitesTableSeeder::class,
            RelationsTableSeeder::class,
        ]);

        // Seeds for local and staging
        if (App::environment(['local', 'staging'])) {
            $this->call([
                //
            ]);
        }
    }
}
