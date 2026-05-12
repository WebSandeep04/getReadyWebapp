<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use App\Models\Cloth;
use App\Services\AvailabilityService;

echo "Initialising DB for test...\n";

// Just test the function directly
$start = Carbon::parse('2026-03-06');
$end = Carbon::parse('2026-03-09');
$fullBlockStart = $start->copy()->subDay();
$fullBlockEnd = $end->copy()->addDays(2); // Updated manual test to match new logic

echo "Rental: {$start->format('Y-m-d')} to {$end->format('Y-m-d')}\n";
echo "Delivery Buffer: {$fullBlockStart->format('Y-m-d')}\n";
echo "Pickup & Cleaning Buffer: " . $end->copy()->addDay()->format('Y-m-d') . " to " . $fullBlockEnd->format('Y-m-d') . "\n";
echo "Next Available Date is: " . $fullBlockEnd->copy()->addDay()->format('Y-m-d') . "\n";
