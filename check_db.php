<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== All Events ===\n";
$events = DB::select('SELECT id, evn_title FROM ems_event ORDER BY id');
foreach($events as $event) {
    echo "Event ID: {$event->id}, Title: {$event->evn_title}\n";
}

echo "\n=== Testing API for All Events ===\n";
foreach($events as $event) {
    echo "\n--- Event ID: {$event->id} ({$event->evn_title}) ---\n";
    
    // Test participants API
    $statistics = DB::table('ems_connect')
        ->where('con_event_id', $event->id)
        ->where('con_delete_status', 'active')
        ->selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN con_answer = "accept" THEN 1 ELSE 0 END) as attending,
            SUM(CASE WHEN con_answer = "denied" THEN 1 ELSE 0 END) as not_attending,
            SUM(CASE WHEN con_answer = "invalid" THEN 1 ELSE 0 END) as pending
        ')
        ->first();

    echo "Total: {$statistics->total}, Attending: {$statistics->attending}, Not Attending: {$statistics->not_attending}, Pending: {$statistics->pending}\n";
    
    // Test department participation
    if ($statistics->total > 0) {
        $deptStats = DB::table('ems_connect')
            ->join('ems_employees', 'ems_connect.con_employee_id', '=', 'ems_employees.id')
            ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
            ->where('ems_connect.con_event_id', $event->id)
            ->where('ems_connect.con_delete_status', 'active')
            ->selectRaw('
                ems_department.dpm_name as name,
                SUM(CASE WHEN ems_connect.con_answer = "accept" THEN 1 ELSE 0 END) as attending,
                SUM(CASE WHEN ems_connect.con_answer = "denied" THEN 1 ELSE 0 END) as notAttending,
                SUM(CASE WHEN ems_connect.con_answer = "invalid" THEN 1 ELSE 0 END) as pending
            ')
            ->groupBy('ems_department.id', 'ems_department.dpm_name')
            ->get();
        
        foreach($deptStats as $dept) {
            echo "  Dept: {$dept->name} - Attending: {$dept->attending}, Not: {$dept->notAttending}, Pending: {$dept->pending}\n";
        }
    } else {
        echo "  No participants found\n";
    }
}