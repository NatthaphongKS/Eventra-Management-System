<?php
/**
 * ชื่อไฟล์: ImportService.php
 * คำอธิบาย: Service สำหรับจัดการการนำเข้าข้อมูลพนักงานแบบ Bulk
 * ชื่อผู้เขียน/แก้ไข: Thanusin leenarat
 * วันที่จัดทำ/แก้ไข: 27 กุมภาพันธ์ 2569
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
        $empId = trim($row['emp_id'] ?? '');
        $email = trim($row['emp_email'] ?? '');
        $phone = preg_replace('/\D+/', '', $row['emp_phone'] ?? '');
        $firstName = trim(mb_strtolower($row['emp_firstname'] ?? ''));
        $lastName = trim(mb_strtolower($row['emp_lastname'] ?? ''));

        if (!$empId || !$firstName || !$lastName || !$email || !$phone) {
            return [
                'status' => 'failed',
                'data' => [
                    'emp_id' => $empId,
                    'reason' => 'Missing required fields',
                ],
            ];
        }

        $duplicate = Employee::where(function ($q) use ($empId, $email, $phone, $firstName, $lastName) {
            $q->where('emp_id', $empId)
                ->orWhere('emp_email', $email)
                ->orWhere('emp_phone', $phone)
                ->orWhere(function ($qq) use ($firstName, $lastName) {
                    $qq->whereRaw('LOWER(TRIM(emp_firstname)) = ?', [$firstName])
                        ->whereRaw('LOWER(TRIM(emp_lastname)) = ?', [$lastName]);
                });
        })->exists();

        if ($duplicate) {
            return [
                'status' => 'failed',
                'data' => [
                    'emp_id' => $empId,
                    'reason' => 'Duplicate data in system',
                ],
            ];
        }

        $employee = Employee::create([
            'emp_company_id' => $row['emp_company_id'],
            'emp_id' => $empId,
            'emp_prefix' => $row['emp_prefix'],
            'emp_firstname' => $firstName,
            'emp_lastname' => $lastName,
            'emp_nickname' => $row['emp_nickname'] ?? null,
            'emp_email' => $email,
            'emp_phone' => $phone,
            'emp_position_id' => $row['emp_position_id'],
            'emp_department_id' => $row['emp_department_id'],
            'emp_team_id' => $row['emp_team_id'],
            'emp_password' => null,
            'emp_permission' => 'employee',
            'emp_delete_status' => 'active',
            'emp_created_at' => now(),
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
