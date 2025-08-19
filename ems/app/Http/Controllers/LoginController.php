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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $employee = Employee::where('emp_email', $request->email)->first();

        if (!$employee) {
            return response()->json(['message' => 'Email not found'], 404);
        }
        if ($employee->emp_status === 'disabled') {
            return response()->json(['message' => 'Account can not login'], 403);
        }

        if ($employee->emp_delete_status === 'inactive') {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        if (!Hash::check($request->password, $employee->emp_password)) {
            return response()->json(['message' => 'Incorrect password'], 401);
        }

        Auth::login($employee);
        $request->session()->regenerate();

        // ส่ง success กลับ
        return response()->json([
            'message' => 'Login success',
            'redirect' => '/employee' // path ใน Vue
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logout success']);
    }
}
