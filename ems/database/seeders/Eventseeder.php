<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;

class Eventseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Event::create([
            'evn_title' => 'ประชุมวางแผนงานประจำปี',
            'evn_category_id' => 1,
            'evn_date' => '2023-08-01',
            'evn_timestart' => '2023-08-01 09:00:00',
            'evn_timeend' => '2023-08-01 11:00:00',
            'evn_duration' => 2.0,
            'evn_location' => 'ห้องประชุม A',
            'evn_file' => 'not_have',
            'evn_create_by' => 1,
            'evn_created_at' => now(),
            'evn_status' => 'done',
        ]);
    }
}
