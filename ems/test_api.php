<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Import controller
use App\Http\Controllers\EventController;

echo "=== Testing API Methods Directly ===\n";

$controller = new EventController();

echo "\n--- Testing getEventParticipants for Event 2 ---\n";
try {
    $response = $controller->getEventParticipants(2);
    $data = json_decode($response->getContent(), true);
    echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- Testing getEventParticipationByDepartment for Event 2 ---\n";
try {
    $response = $controller->getEventParticipationByDepartment(2);
    $data = json_decode($response->getContent(), true);
    echo "Response: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- Testing for Event 14 (mixed results) ---\n";
try {
    $response = $controller->getEventParticipants(14);
    $data = json_decode($response->getContent(), true);
    echo "Event 14 participants: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    $response2 = $controller->getEventParticipationByDepartment(14);
    $data2 = json_decode($response2->getContent(), true);
    echo "Event 14 departments: " . json_encode($data2, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}