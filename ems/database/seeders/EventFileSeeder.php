<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventFileSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ems_event_files')->insert([
            [
                'file_event_id' => 1,
                'file_name' => 'retrospective_1-7_Byteforge_2025-08-05.pdf',
                'file_path' => 'events/1/kggBiLKla798hluQfJrTuzgSIXmeME9GZv8E42Sw.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 158047,
                'uploaded_at' => '2025-08-28 14:05:41',
            ],
            [
                'file_event_id' => 2,
                'file_name' => 'retrospective_1-7_Byteforge_2025-08-05.pdf',
                'file_path' => 'events/2/u3yoO1cjNw01ydr3vJO2FPqQme23S6a8sypfGMqQ.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 158047,
                'uploaded_at' => '2025-08-30 15:20:57',
            ],
            [
                'file_event_id' => 3,
                'file_name' => 'Lab2.pdf',
                'file_path' => 'events/3/o0TK9ljol99ofHLIPcsLadPak5S5iQfdtiMvRhjy.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 265659,
                'uploaded_at' => '2025-08-30 16:28:54',
            ],
        ]);
    }
}
