<?php

/**
 * ชื่อไฟล์: PositionController.php
 * คำอธิบาย: Controller สำหรับจัดการ Position API
 * วันที่จัดทำ/แก้ไข: 04 มีนาคม 2569
 */

namespace App\Http\Controllers;

use App\Service\PositionServices\PositionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    protected PositionService $service;

    /**
     * Constructor
     */
    public function __construct(PositionService $service)
    {
        $this->service = $service;
    }

    /**
     * ดึงรายการ Position ทั้งหมด
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * ดึงข้อมูล Position รายคน
     */
    public function show($id)
    {
        return $this->service->show($id);
    }

    /**
     * สร้าง Position ใหม่
     */
    public function store(Request $request)
    {
        return $this->service->store($request);
    }

    /**
     * อัปเดต Position
     */
    public function update(Request $request, $id)
    {
        return $this->service->update($request, $id);
    }

    /**
     * ลบ Position
     */
    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    /**
     * ดึง Positions ตาม Team ID
     */
    public function getByTeam($teamId)
    {
        return $this->service->getByTeam($teamId);
    }

    /**
     * ดึงข้อมูล Position ทั้งหมดสำหรับ Dropdown
     */
    public function getAll()
    {
        return $this->service->getAll();
    }
}
