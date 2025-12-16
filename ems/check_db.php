<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking Database Connection..." . PHP_EOL;
    $count = App\Models\Event::count();
    echo "Event Count: " . $count . PHP_EOL;
    
    $empCount = App\Models\Employee::count();
    echo "Employee Count: " . $empCount . PHP_EOL;
    
    // Check first event if exists
    if ($count > 0) {
        $first = App\Models\Event::first();
        echo "First Event: " . $first->evn_title . " (Status: " . $first->evn_status . ")" . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
