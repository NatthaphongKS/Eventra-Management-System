<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee; // Assuming you have an Employee model

class LoginController extends Controller
{

    public function index()
    {
        return response()->json(['redirect' => '/login']);
    }
    public function login(Request $request)
    {
        // 1. Validate: Let Laravel handle ValidationException automatically (returns 422)
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Find Employee
        $employee = Employee::where('emp_email', $request->email)->first();

        // 3. Check Credentials
        // Combine checks to prevent user enumeration if desired,
        // though specific messages are helpful for legitimate users in internal apps.
        if (!$employee || !Hash::check($request->password, $employee->emp_password)) {
            return response()->json(['message' => 'Incorrect username or password. Please try again'], 401);
        }

        // 4. Check Status
        if ($employee->emp_status === 'disabled') {
            return response()->json(['message' => 'Account is disabled'], 403);
        }

        if ($employee->emp_delete_status === 'inactive') {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        // 5. Login
        try {
            Auth::login($employee);
            $request->session()->regenerate();
            
            $response = response()->json([
                'redirect' => '/',
                'user' => $employee, // Optional: return user info if needed
                'message' => 'Login successful'
            ]);

            return $response;
        } catch (\Exception $e) {
            // Only catch unexpected system errors during session regeneration or login
            \Illuminate\Support\Facades\Log::error('Login system error', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            return response()->json(['message' => 'Login failed due to system error.'], 500);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logout success'])
            ->withCookie(cookie()->forget(config('session.cookie')));
    }
    public function showProfile()
    {
        // ตรวจสอบก่อนว่ามีการ Login หรือยัง
        if (Auth::check()) {
            // ดึง Model Employee ของผู้ใช้ที่ Login อยู่
            $employee = Auth::user(); 
            
            return response()->json( [
                'employee' => $employee 
            ]);
        }
        
        // ถ้ายังไม่ได้ Login ก็ให้ Redirect ไปหน้า Login
        return redirect('/login');
    }
}
