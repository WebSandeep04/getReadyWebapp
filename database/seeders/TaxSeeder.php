<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            ['name' => 'Gst 18%', 'percentage' => 18.00],
            ['name' => 'Cgst 9%', 'percentage' => 9.00],
            ['name' => 'Sgst 9%', 'percentage' => 9.00],
            ['name' => 'Igst 18%', 'percentage' => 18.00],
            ['name' => 'Tcs 10%', 'percentage' => 10.00],
        ];

        foreach ($taxes as $tax) {
            Tax::updateOrCreate(['name' => $tax['name']], $tax);
        }
    }
}
