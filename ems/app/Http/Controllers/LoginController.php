<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginService;

/**
 * Handles authentication-related HTTP endpoints.
 *
 * Responsibilities:
 * - Provide login route redirect
 * - Authenticate user credentials
 * - Create and destroy login sessions
 * - Return authenticated user profile
 */
class LoginController extends Controller
{
    /**
     * Service responsible for authentication and session handling.
     */
    protected LoginService $loginService;

    /**
     * Inject LoginService dependency.
     */
    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    /**
     * Redirect entry point for auth routes.
     */
    public function index()
    {
        // ตอบกลับเป็น URL ปลายทางให้ฝั่ง Frontend พาผู้ใช้ไปหน้าเข้าสู่ระบบ
        return response()->json(['redirect' => '/login']);
    }

    /**
     * Handle login request.
     *
     * Flow:
     * - Validate request payload
     * - Authenticate employee credentials and status
     * - Create authenticated session
     */
    public function login(Request $request)
    {
        // 1) ตรวจสอบข้อมูลที่ส่งเข้ามา
        // - email ต้องมีค่าและอยู่ในรูปแบบอีเมล
        // - password ต้องมีค่า
        // หากไม่ผ่าน Laravel จะโยน ValidationException และตอบกลับ 422 อัตโนมัติ
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2) ตรวจสอบตัวตนผู้ใช้
        // Service จะทำงานรวม: ค้นหาพนักงาน, เช็ครหัสผ่าน, และตรวจสอบสถานะการใช้งาน
        // โครงสร้างผลลัพธ์ที่คาดหวัง: [success, message, status, employee]
        $authResult = $this->loginService->authenticate($request->email, $request->password);

        // ถ้าเข้าสู่ระบบไม่สำเร็จ ให้ส่ง message และ status code ตามที่ service กำหนด
        if (!$authResult['success']) {
            return response()->json(['message' => $authResult['message']], $authResult['status']);
        }

        // 3) สร้าง session หลังยืนยันตัวตนสำเร็จ
        // ใช้ข้อมูล employee ที่ได้จากขั้นตอน authenticate
        $loginResult = $this->loginService->loginSession($authResult['employee'], $request);

        // หากสร้าง session ไม่สำเร็จ ให้ตอบกลับ error ตามผลจาก service
        if (!$loginResult['success']) {
            return response()->json(['message' => $loginResult['message']], $loginResult['status']);
        }

        // สำเร็จ: ส่ง redirect path และข้อมูลผู้ใช้กลับไปให้ฝั่ง client
        return response()->json([
            'redirect' => '/',
            'user' => $authResult['employee'],
        ]);
    }

    /**
     * Logout current authenticated user and invalidate session cookie.
     */
    public function logout(Request $request)
    {
        // ล้างข้อมูลการเข้าสู่ระบบในฝั่งเซิร์ฟเวอร์ (session/auth state)
        $this->loginService->logout($request);

        // ส่งคำตอบพร้อมสั่งให้ browser ลบ session cookie ฝั่ง client
        return response()->json(['message' => 'Logout success'])
            ->withCookie(cookie()->forget(config('session.cookie')));
    }

    /**
     * Return authenticated employee profile.
     *
     * If no authenticated employee exists, redirect to login page.
     */
    public function showProfile()
    {
        // ดึงข้อมูลผู้ใช้ที่ล็อกอินอยู่จาก context ปัจจุบัน
        $employee = $this->loginService->getAuthenticatedEmployee();

        // หากพบผู้ใช้ ให้ส่งข้อมูลโปรไฟล์กลับในรูปแบบ JSON
        if ($employee) {
            return response()->json(['employee' => $employee]);
        }

        // หากยังไม่ล็อกอิน ให้พาไปหน้า login
        return redirect('/login');
    }
}
