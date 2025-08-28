<?php
/*
 * @Author: Nattapong Kamma Icezazarun@gmail.com
 * @Date: 2025-08-13 15:19:51
 * @LastEditors: Nattapong Kamma Icezazarun@gmail.com
 * @LastEditTime: 2025-08-22 21:47:54
 * @FilePath: \Eventra-Management-System\ems\app\Models\Category.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $table = 'ems_categories';

    // ให้ตรงกับชื่อคอลัมน์ใน DB
    protected $fillable = [
        'cat_name',
        'cat_deleted_status',
        'cat_created_at',
        'cat_created_by',
        'cat_deleted_at',
        'cat_deleted_by',

    ];

    protected $casts = [
        'cat_created_at' => 'datetime',
        'cat_deleted_at' => 'datetime',
    ];


    // ตารางนี้ไม่มี created_at/updated_at
    public $timestamps = false;

    // ถ้ามีโมเดล Event อยู่ใน App\Models\Event
    public function events()
    {
        return $this->hasMany(Event::class, 'evn_category_id');
    }
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'cat_created_by');
    }
    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'cat_deleted_by');
    }
}
