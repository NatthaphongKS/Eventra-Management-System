<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event; // Assuming you have an Event model
use App\Models\Category;

class EventController extends Controller
{
    public function eventInfo()
    {
        $categories = Category::select('id', 'cat_name')
            ->where('cat_delete_status', 'active')
            ->orderBy('cat_name')
            ->get();
        return response()->json(['categories' => $categories]);
    }
}
