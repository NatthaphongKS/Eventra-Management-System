<?php

/**
 * ชื่อไฟล์: ImportService.php
 * คำอธิบาย: Service สำหรับจัดการการนำเข้าข้อมูลพนักงานแบบ Bulk
 * ชื่อผู้เขียน/แก้ไข: Team ByteForge
 * วันที่จัดทำ/แก้ไข: 20 กุมภาพันธ์ 2569
 */

namespace App\Service\EmployeesServices;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ImportService
{
    /**
     * ชื่อฟังก์ชัน: importBulk
     * คำอธิบาย: นำเข้าข้อมูลพนักงานแบบหลายรายการ (Bulk Import)
     * Input: Request $request (rows[])
     * Output: JSON สรุปผลการ import (success / partial / failed)
     */
    public function importBulk(Request $request)
    {
        $rows = $request->input('rows', []);

        if (!is_array($rows) || count($rows) === 0) {
            return response()->json([
                'message' => 'No rows provided.',
            ], 422);
        }

        $created = [];
        $failed = [];

        foreach ($rows as $row) {
            try {
                $result = $this->processRow($row);

                if ($result['status'] === 'success') {
                    $created[] = $result['data'];
                } else {
                    $failed[] = $result['data'];
                }
            } catch (\Throwable $exception) {
                Log::error('EMP_BULK_IMPORT_FAIL', [
                    'error' => $exception->getMessage(),
                ]);

                $failed[] = [
                    'emp_id' => $row['employeeId'] ?? null,
                    'reason' => 'System error',
                ];
            }
        }

        return response()->json([
            'message' => count($created) && count($failed)
                ? 'partial'
                : (count($created) ? 'success' : 'failed'),
            'created' => $created,
            'failed' => $failed,
        ], 200);
    }

    /**
     * ชื่อฟังก์ชัน: processRow
     * คำอธิบาย: ประมวลผลข้อมูลพนักงานทีละแถว
     * Input: array $row
     * Output: array สถานะการสร้างข้อมูล
     */
    private function processRow(array $row): array
    {
        $empId = trim($row['employeeId'] ?? '');
        $email = trim($row['email'] ?? '');
        $phone = preg_replace('/\D+/', '', $row['phone'] ?? '');

        $duplicate = Employee::where(function ($query) use ($empId, $email, $phone) {
            $query->where('emp_id', $empId)
                ->orWhere('emp_email', $email)
                ->orWhere('emp_phone', $phone);
        })->exists();

        if ($duplicate) {
            return [
                'status' => 'failed',
                'data' => [
                    'emp_id' => $empId,
                    'reason' => 'Duplicate emp_id / email / phone',
                ],
            ];
        }

        $employee = Employee::create([
            'emp_company_id' => Auth::user()->emp_company_id ?? 1,
            'emp_id' => $empId,
            'emp_prefix' => 1,
            'emp_firstname' => trim($row['name'] ?? ''),
            'emp_lastname' => '',
            'emp_nickname' => trim($row['nickname'] ?? ''),
            'emp_email' => $email,
            'emp_phone' => $phone,
            'emp_password' => Hash::make('Password123'),
            'emp_permission' => 'employee',
            'emp_delete_status' => 'active',
            'emp_created_at' => Carbon::now(),
            'emp_create_by' => Auth::id(),
        ]);

        return [
            'status' => 'success',
            'data' => [
                'id' => $employee->id,
                'emp_id' => $employee->emp_id,
            ],
        ];
    }
}
