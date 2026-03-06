<?php

/**
 * ชื่อไฟล์: TeamController.php
 * คำอธิบาย: Controller สำหรับจัดการ Team API
 * ผู้จัดทำ: ณัฐพงศ์ คงศิลป์
 * วันที่จัดทำ/แก้ไข: 04 มีนาคม 2569
 */

namespace App\Http\Controllers;

use App\Service\TeamServices\TeamService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected TeamService $service;

    /**
     * Constructor
     */
    public function __construct(TeamService $service)
    {
        $this->service = $service;
    }

    /**
     * ดึงรายการ Team ทั้งหมด
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * ดึงข้อมูล Team รายคน
     */
    public function show($id)
    {
        return $this->service->show($id);
    }

    /**
     * สร้าง Team ใหม่
     */
    public function store(Request $request)
    {
        return $this->service->store($request);
    }

    /**
     * อัปเดต Team
     */
    public function update(Request $request, $id)
    {
        return $this->service->update($request, $id);
    }

    /**
     * ลบ Team
     */
    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    /**
     * ดึง Teams ตาม Department ID
     */
    public function getByDepartment($departmentId)
    {
        return $this->service->getByDepartment($departmentId);
    }

    /**
     * ดึงข้อมูล Team ทั้งหมดสำหรับ Dropdown
     */
    public function getAll()
    {
        return $this->service->getAll();
    }
}
