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
            [
                'file_event_id' => 4,
                'file_name' => 'meeting_minutes_2025-09-01.pdf',
                'file_path' => 'events/4/Xk20sdfQWEasdhJASD9087sadljqweUasd123kL.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 189340,
                'uploaded_at' => '2025-09-01 10:15:32',
            ],
            [
                'file_event_id' => 5,
                'file_name' => 'training_materials_security_2025-09-03.pdf',
                'file_path' => 'events/5/FAsd9823asdJkl9087asdLLsdf21ZxcvbnMqw.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 245870,
                'uploaded_at' => '2025-09-03 13:42:11',
            ],
            [
                'file_event_id' => 6,
                'file_name' => 'annual_plan_presentation_2025-09-05.pdf',
                'file_path' => 'events/6/Lk9pO0QweKjh213asdMNB45zxcv9087WERtyu.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 310452,
                'uploaded_at' => '2025-09-05 09:58:44',
            ],
        ]);
    }
}
