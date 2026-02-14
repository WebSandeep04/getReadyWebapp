<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\State;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maharashtra = State::where('name', 'Maharashtra')->first();
        $gujarat = State::where('name', 'Gujarat')->first();
        $karnataka = State::where('name', 'Karnataka')->first();
        $delhi = State::where('name', 'Delhi')->first();
        $rajasthan = State::where('name', 'Rajasthan')->first();

        $cities = [
            ['state_id' => $maharashtra->id, 'name' => 'Mumbai', 'status' => 1],
            ['state_id' => $maharashtra->id, 'name' => 'Pune', 'status' => 1],
            ['state_id' => $gujarat->id, 'name' => 'Ahmedabad', 'status' => 1],
            ['state_id' => $gujarat->id, 'name' => 'Surat', 'status' => 1],
            ['state_id' => $karnataka->id, 'name' => 'Bangalore', 'status' => 1],
            ['state_id' => $delhi->id, 'name' => 'New Delhi', 'status' => 1],
            ['state_id' => $rajasthan->id, 'name' => 'Jaipur', 'status' => 1],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name' => $city['name'], 'state_id' => $city['state_id']], 
                ['status' => $city['status']]
            );
        }
    }
}
