<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    public function index()
    {
        return Category::query()
            ->where('cat_delete_status', 'active')   // เอาเฉพาะที่ active
            ->orderBy('cat_name')
            ->get(['id','cat_name']);   // ส่งกลับแค่ฟิลด์ที่ต้องใช้
    }
}

