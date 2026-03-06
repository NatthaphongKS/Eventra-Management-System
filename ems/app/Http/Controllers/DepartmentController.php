<?php

/**
 * ชื่อไฟล์: DepartmentController.php
 * คำอธิบาย: Controller สำหรับจัดการ Department API
 * ผู้จัดทำ: ณัฐพงศ์ คงศิลป์
 * วันที่จัดทำ/แก้ไข: 04 มีนาคม 2569
 */

namespace App\Http\Controllers;

use App\Service\DepartmentServices\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected DepartmentService $service;

    /**
     * Constructor
     */
    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    /**
     * ดึงรายการ Department ทั้งหมด
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * ดึงข้อมูล Department รายคน
     */
    public function show($id)
    {
        return $this->service->show($id);
    }

    /**
     * สร้าง Department ใหม่
     */
    public function store(Request $request)
    {
        return $this->service->store($request);
    }

    /**
     * อัปเดต Department
     */
    public function update(Request $request, $id)
    {
        return $this->service->update($request, $id);
    }

    /**
     * ลบ Department
     */
    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    /**
     * ดึงข้อมูล Department ทั้งหมดสำหรับ Dropdown
     */
    public function getAll()
    {
        return $this->service->getAll();
    }
}
