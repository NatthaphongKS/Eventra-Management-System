<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Service สำหรับจัดการขั้นตอนการเข้าสู่ระบบ/ออกจากระบบของพนักงาน
 * และการดึงผู้ใช้ที่ล็อกอินอยู่ในปัจจุบัน
 */
class LoginService
{
    /**
     * Authenticate an employee with the given credentials.
     *
     * @param string $email
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'status' => int, 'employee' => ?Employee]
     */
    public function authenticate(string $email, string $password): array
    {
        // 1) ค้นหาพนักงานจากอีเมลที่ผู้ใช้กรอกเข้ามา
        $employee = Employee::where('emp_email', $email)->first();

        // 2) ตรวจสอบตัวตน
        // - ต้องพบพนักงานในระบบ
        // - รหัสผ่านที่ส่งมาต้องตรงกับ hash ในฐานข้อมูล
        if (!$employee || !Hash::check($password, $employee->emp_password)) {
            return [
                'success' => false,
                'message' => 'Incorrect username or password. Please try again',
                'status' => 401,
                'employee' => null,
            ];
        }

        // 3) ตรวจสอบสถานะบัญชี (disabled = ถูกปิดการใช้งาน)
        if ($employee->emp_status === 'disabled') {
            return [
                'success' => false,
                'message' => 'Account is disabled',
                'status' => 403,
                'employee' => null,
            ];
        }

        // 4) ตรวจสอบสถานะการลบ/ยกเลิกบัญชี (inactive = ไม่อนุญาตให้เข้าใช้งาน)
        if ($employee->emp_delete_status === 'inactive') {
            return [
                'success' => false,
                'message' => 'Account is inactive',
                'status' => 403,
                'employee' => null,
            ];
        }

        // ผ่านทุกเงื่อนไข: อนุญาตให้เข้าสู่ขั้นตอนสร้าง session
        return [
            'success' => true,
            'message' => 'Authenticated',
            'status' => 200,
            'employee' => $employee,
        ];
    }

    /**
     * Log the employee in and regenerate the session.
     *
     * @param Employee $employee
     * @param Request $request
     * @return array ['success' => bool, 'message' => string, 'status' => int]
     */
    public function loginSession(Employee $employee, Request $request): array
    {
        try {
            // 1) สร้างสถานะล็อกอินให้ผู้ใช้ในระบบ Laravel Auth
            Auth::login($employee);

            // 2) regenerate session id เพื่อลดความเสี่ยง Session Fixation
            $request->session()->regenerate();

            // 3) ตอบกลับผลลัพธ์การล็อกอินสำเร็จ
            return [
                'success' => true,
                'message' => 'Login successful',
                'status' => 200,
            ];
        } catch (\Exception $e) {
            // หากเกิดข้อผิดพลาดระหว่างสร้าง session ให้บันทึก log เพื่อใช้ตรวจสอบย้อนหลัง
            Log::error('Login system error', [
                'error' => $e->getMessage(),
                'email' => $employee->emp_email,
            ]);

            // ส่งผลลัพธ์ล้มเหลวกลับไปที่ controller
            return [
                'success' => false,
                'message' => 'Login failed due to system error.',
                'status' => 500,
            ];
        }
    }

    /**
     * Log the employee out and invalidate the session.
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        // 1) ออกจากระบบในฝั่ง Auth
        Auth::logout();

        // 2) ทำลาย session เดิมทั้งหมด
        $request->session()->invalidate();

        // 3) สร้าง CSRF token ใหม่สำหรับ request ถัดไป
        $request->session()->regenerateToken();
    }

    /**
     * Get the currently authenticated employee.
     *
     * @return Employee|null
     */
    public function getAuthenticatedEmployee(): ?Employee
    {
        // ตรวจสอบว่าปัจจุบันมีผู้ใช้ล็อกอินอยู่หรือไม่
        if (Auth::check()) {
            // คืนค่า user ที่อยู่ใน session ปัจจุบัน และแปลงเป็น Employee
            // return Auth::user();
            return Auth::user() instanceof Employee ? Auth::user() : null;
        }

        // ไม่มีผู้ใช้ล็อกอิน
        return null;
    }
}
