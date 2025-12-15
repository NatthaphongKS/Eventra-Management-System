<?php
// Test database query directly
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Testing Event Statistics Query ===\n\n";

// Test with event ID 3 (from the screenshot)
$eventIds = [3];

echo "Testing with event_ids: " . json_encode($eventIds) . "\n\n";

// Test basic connect data
echo "--- Basic Connect Data ---\n";
$connects = DB::table('ems_connect')
    ->whereIn('con_event_id', $eventIds)
    ->get();
echo "Total connects: " . $connects->count() . "\n";
foreach ($connects as $conn) {
    echo "Event: {$conn->con_event_id}, Employee: {$conn->con_employee_id}, Answer: {$conn->con_answer}, Status: {$conn->con_delete_status}\n";
}

echo "\n--- Statistics Query ---\n";
$stats = DB::table('ems_connect')
    ->whereIn('con_event_id', $eventIds)
    ->where('con_delete_status', 'active')
    ->selectRaw('
        COUNT(*) as total_participation,
        SUM(CASE WHEN con_answer = "accept" THEN 1 ELSE 0 END) as attending,
        SUM(CASE WHEN con_answer = "denied" THEN 1 ELSE 0 END) as not_attending,
        SUM(CASE WHEN con_answer = "invalid" OR con_answer IS NULL THEN 1 ELSE 0 END) as pending
    ')
    ->first();

echo "Total Participation: {$stats->total_participation}\n";
echo "Attending: {$stats->attending}\n";
echo "Not Attending: {$stats->not_attending}\n";
echo "Pending: {$stats->pending}\n";

echo "\n--- Department Breakdown ---\n";
$departments = DB::table('ems_connect')
    ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
    ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
    ->whereIn('ems_connect.con_event_id', $eventIds)
    ->where('ems_connect.con_delete_status', 'active')
    ->groupBy('ems_department.id', 'ems_department.dpm_name')
    ->selectRaw('
        ems_department.dpm_name as name,
        SUM(CASE WHEN ems_connect.con_answer = "accept" THEN 1 ELSE 0 END) as attending,
        SUM(CASE WHEN ems_connect.con_answer = "denied" THEN 1 ELSE 0 END) as notAttending,
        SUM(CASE WHEN ems_connect.con_answer = "invalid" OR ems_connect.con_answer IS NULL THEN 1 ELSE 0 END) as pending
    ')
    ->get();

foreach ($departments as $dept) {
    echo "{$dept->name}: Attending={$dept->attending}, Not Attending={$dept->notAttending}, Pending={$dept->pending}\n";
}

echo "\n--- Participants ---\n";
$participants = DB::table('ems_connect')
    ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
    ->leftJoin('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
    ->whereIn('ems_connect.con_event_id', $eventIds)
    ->where('ems_connect.con_delete_status', 'active')
    ->select(
        'ems_employees.id',
        'ems_employees.emp_id',
        'ems_employees.emp_firstname',
        'ems_employees.emp_lastname',
        'ems_department.dpm_name as department',
        'ems_connect.con_answer as status'
    )
    ->get();

echo "Total participants: " . $participants->count() . "\n";
foreach ($participants as $p) {
    echo "ID: {$p->id}, Name: {$p->emp_firstname} {$p->emp_lastname}, Dept: {$p->department}, Status: {$p->status}\n";
}

echo "\n=== Done ===\n";
