<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginService;

class LoginController extends Controller
{
    protected LoginService $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->loginService = $loginService;
    }

    public function index()
    {
        return response()->json(['redirect' => '/login']);
    }

    public function login(Request $request)
    {
        // 1. Validate: Let Laravel handle ValidationException automatically (returns 422)
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Authenticate (find employee + check credentials + check status)
        $authResult = $this->loginService->authenticate($request->email, $request->password);

        if (!$authResult['success']) {
            return response()->json(['message' => $authResult['message']], $authResult['status']);
        }

        // 3. Login session
        $loginResult = $this->loginService->loginSession($authResult['employee'], $request);

        if (!$loginResult['success']) {
            return response()->json(['message' => $loginResult['message']], $loginResult['status']);
        }

        return response()->json([
            'redirect' => '/',
            'user' => $authResult['employee'],
        ]);
    }

    public function logout(Request $request)
    {
        $this->loginService->logout($request);

        return response()->json(['message' => 'Logout success'])
            ->withCookie(cookie()->forget(config('session.cookie')));
    }

    public function showProfile()
    {
        $employee = $this->loginService->getAuthenticatedEmployee();

        if ($employee) {
            return response()->json(['employee' => $employee]);
        }

        return redirect('/login');
    }
}
