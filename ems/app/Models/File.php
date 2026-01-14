<?php
/*
Model : File
Edit by : Chitdanai
*/
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'ems_event_files';
    public $timestamps = false;

    protected $fillable = [
        'file_event_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_at', // ใช้ชื่อตามภาพ DB จริง
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_path' => 'string', // บังคับให้เป็น string เพื่อป้องกันการบันทึกเป็น 0
    ];
}
