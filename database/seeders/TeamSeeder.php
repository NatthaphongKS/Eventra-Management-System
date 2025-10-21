<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Department;
use App\Models\Position;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            'Mobile',
            'Graphic',
            'SMS-SALE&SUPPORT',
            'SMS-Dev',
            'Accounting',
            'Team Project-1',
            'Team Project-2',
            'Team Project-3',
            'Team Project-4',
            'Team Project-5',
            'Management',
            'Quality Assurance',
            'eMeeting',
            'System Engineer',
            'IT Support',
            'System Integration',
            'SALE-PROJECT',
            'Artificial Intelligence',
            'Housekeeper',
            'Human Resource',
            'Interactive Media',
            'MWE-SALE&SUPPORT',
            'MWE-DEV',
            'MWE-Graphic',
            'Digital Marketing',
            'Chatcone-Dev',
            'Chatcone-SALE&Support',
            'Developer',
        ];

        foreach ($teams as $name) {
            DB::table('ems_team')->insert([
                'tm_name' => $name,
                'tm_delete_status' => 'active',
            ]);
        }

    }
}
