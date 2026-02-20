<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\LoginServices\LoginService;

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
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $authResult = $this->loginService->authenticate(
            $credentials['email'],
            $credentials['password']
        );

        if (!$authResult['success']) {
            return response()->json(
                ['message' => $authResult['message']],
                $authResult['status']
            );
        }

        $loginResult = $this->loginService->loginSession($authResult['employee'], $request);

        if (!$loginResult['success']) {
            return response()->json(
                ['message' => $loginResult['message']],
                $loginResult['status']
            );
        }

        return response()->json([
            'message' => $loginResult['message'],
            'redirect' => '/dashboard',
            'user' => $authResult['employee'],
        ], $loginResult['status']);
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
