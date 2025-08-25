<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class HistoryEmployeeController extends Controller
{
    public function getEmployees_History()
    {
       return Employee::where('ems_employees', 'inactive')
                       ->orderBy('cat_name')
                       ->get();
    }
}
