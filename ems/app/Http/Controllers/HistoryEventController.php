<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Category;
use App\Models\Event;

class HistoryEventController extends Controller
{
    public function eventInfo()
    {
        $events = Event::join('ems_categories', 'ems_event.evn_category_id', '=', 'ems_categories.id')
            ->where('evn_status', 'deleted')->get();
        return response()->json($events);
    }
}
