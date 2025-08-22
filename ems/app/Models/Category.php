<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'ems_categories';

    // ให้ตรงกับชื่อคอลัมน์ใน DB
    protected $fillable = [
        'cat_name',
        'cat_delete_status',
    ];

    // ตารางนี้ไม่มี created_at/updated_at
    public $timestamps = false;

    // ถ้ามีโมเดล Event อยู่ใน App\Models\Event
    public function events()
    {
        // foreign key ของตาราง events ที่อ้างถึง categories
        // เปลี่ยน 'event_category_id' ให้ตรงกับคอลัมน์จริง
        return $this->hasMany(Event::class, 'event_category_id', 'id');
    }

    // ใช้กรองรายการที่ยัง active
    public function scopeActive($q)
    {
        return $q->where('cat_delete_status', 'active');
    }
}
