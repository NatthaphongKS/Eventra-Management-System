<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConnectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ems_connect')->insert([
    [
        'con_event_id' => 1, // event id ต้องมีอยู่ใน ems_event
        'con_employee_id' => 1,
        'con_answer' => 'invalid',
        'con_reason' => null,
        'con_delete_status' => 'active',
        'con_checkin_status' => 1,
    ],
    [
        'con_event_id' => 1,
        'con_employee_id' => 2,
        'con_answer' => 'accept',
        'con_reason' => null,
        'con_delete_status' => 'active',
        'con_checkin_status' => 1,
    ],
    [
        'con_event_id' => 2,
        'con_employee_id' => 3,
        'con_answer' => 'accept',
        'con_reason' => null,
        'con_delete_status' => 'active',
        'con_checkin_status' => 0,
    ],
    [
        'con_event_id' => 3,
        'con_employee_id' => 1,
        'con_answer' => 'denied',
        'con_reason' => 'ขี้เกียจไป',
        'con_delete_status' => 'active',
        'con_checkin_status' => 0,
    ],
    ]);
    }
}
