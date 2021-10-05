<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $list = [
            'My Family',
            'My Friends',
            'My Trust Circle',
            'My Clients',
            'My Colleagues'
        ];

        foreach ($list as $name) {
            Group::factory()->create([
                'name' => $name
            ]);
        }
    }
}
