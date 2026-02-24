<?php

/**
 * ชื่อไฟล์: EmployeeController.php
 * คำอธิบาย: Controller สำหรับจัดการ Employee API
 * ชื่อผู้เขียน/แก้ไข: Thanusin leenarat
 * วันที่จัดทำ/แก้ไข: 20 กุมภาพันธ์ 2569
 */

namespace App\Http\Controllers;

use App\Service\EmployeesServices\EmployeesService;
use App\Service\EmployeesServices\ImportService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    protected EmployeesService $service;
    protected ImportService $importService;

    /**
     * Constructor
     */
    public function __construct(
        EmployeesService $service,
        ImportService $importService
    ) {
        $this->service = $service;
        $this->importService = $importService;
    }

    /**
     * ดึงรายการพนักงานทั้งหมด
     */
    public function index()
    {
        return $this->service->index();
    }

    /**
     * ดึงข้อมูลพนักงานรายคน
     */
    public function show($id)
    {
        return $this->service->show($id);
    }

    /**
     * ดึงข้อมูล metadata สำหรับ dropdown
     */
    public function meta()
    {
        return $this->service->meta();
    }

    /**
     * สร้างพนักงานใหม่
     */
    public function store(Request $request)
    {
        return $this->service->store($request);
    }

    /**
     * แก้ไขข้อมูลพนักงาน
     */
    public function update(Request $request, $id)
    {
        return $this->service->update($request, $id);
    }

    /**
     * Soft delete (custom)
     */
    public function softDelete($id)
    {
        return $this->service->softDelete($id);
    }

    /**
     * Soft delete (standard route)
     */
    public function destroy($id)
    {
        return $this->service->destroy($id);
    }

    /**
     * Import พนักงานแบบ Bulk
     */
    public function importBulk(Request $request)
    {
        return $this->importService->importBulk($request);
    }

    /**
     * บันทึก Department
     */
    public function saveDepartment(Request $request)
    {
        return $this->service->saveDepartment($request);
    }

    /**
     * บันทึก Position
     */
    public function savePosition(Request $request)
    {
        return $this->service->savePosition($request);
    }

    /**
     * บันทึก Team
     */
    public function saveTeam(Request $request)
    {
        return $this->service->saveTeam($request);
    }

    /**
     * ตรวจสอบข้อมูลซ้ำ
     */
    public function checkDuplicate(Request $request)
    {
        return $this->service->checkDuplicate($request);
    }
}
