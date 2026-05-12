<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Maharashtra', 'status' => 1],
            ['name' => 'Gujarat', 'status' => 1],
            ['name' => 'Karnataka', 'status' => 1],
            ['name' => 'Delhi', 'status' => 1],
            ['name' => 'Rajasthan', 'status' => 1],
        ];

        foreach ($states as $state) {
            State::updateOrCreate(['name' => $state['name']], $state);
        }
    }
}
