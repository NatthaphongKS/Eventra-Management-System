<?php

/**
 * ชื่อไฟล์: LoginController.php
 * คำอธิบาย: Controller สำหรับจัดการการเข้าสู่ระบบ ออกจากระบบ และแสดงโปรไฟล์ของผู้ใช้
 * ผู้เขียน: Yothin S.
 * วันที่แก้ไข: 20/02/2026
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\LoginServices\LoginService;

class LoginController extends Controller
{
    protected LoginService $loginService;


    /**
     * สร้าง instance ของ LoginService เพื่อใช้ใน controller
     *
     * @param LoginService $loginService
     */
    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }


    /**
     * Redirect ไปหน้า login (API route)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['redirect' => '/login']);
    }


    /**
     * ตรวจสอบข้อมูลเข้าสู่ระบบและสร้าง session ให้ผู้ใช้
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // ตรวจสอบความถูกต้องของข้อมูล email และ password
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // เรียก service เพื่อตรวจสอบสิทธิ์
        $authResult = $this->loginService->authenticate(
            $credentials['email'],
            $credentials['password']
        );

        if (!$authResult['success']) {
            // กรณีรหัสผ่านหรืออีเมลผิด
            return response()->json(
                ['message' => $authResult['message']],
                $authResult['status']
            );
        }

        // สร้าง session ให้ผู้ใช้
        $loginResult = $this->loginService->loginSession($authResult['employee'], $request);

        if (!$loginResult['success']) {
            // กรณีเกิด error ระหว่างสร้าง session
            return response()->json(
                ['message' => $loginResult['message']],
                $loginResult['status']
            );
        }

        // ล็อกอินสำเร็จ ส่งข้อมูลกลับไป
        return response()->json([
            'message' => $loginResult['message'],
            'redirect' => '/',
            'user' => $authResult['employee'],
        ], $loginResult['status']);
    }


    /**
     * ออกจากระบบและลบ session ของผู้ใช้
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // เรียก service เพื่อ logout และลบ session
        $this->loginService->logout($request);

        // ลบ cookie session และแจ้ง logout สำเร็จ
        return response()->json(['message' => 'Logout success'])
            ->withCookie(cookie()->forget(config('session.cookie')));
    }


    /**
     * แสดงข้อมูลโปรไฟล์ของผู้ใช้ที่ล็อกอินอยู่
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function showProfile()
    {
        // ดึงข้อมูล employee ที่ล็อกอินอยู่
        $employee = $this->loginService->getAuthenticatedEmployee();

        if ($employee) {
            // ถ้ามีผู้ใช้ล็อกอิน ส่งข้อมูลกลับ
            return response()->json(['employee' => $employee]);
        }

        // ถ้าไม่มีผู้ใช้ล็อกอิน redirect ไปหน้า login
        return redirect('/login');
    }
}
