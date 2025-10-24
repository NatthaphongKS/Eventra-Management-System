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
        'file_size',
        'file_type',
        'file_upload_at',
        'file_upload_by',
        'file_delete_status',
        'file_delete_by',
    ];
    protected $casts = [
        'file_upload_at' => 'datetime',
    ];
}
