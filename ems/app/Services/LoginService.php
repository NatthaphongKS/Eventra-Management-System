<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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
        // Find Employee
        $employee = Employee::where('emp_email', $email)->first();

        // Check Credentials
        if (!$employee || !Hash::check($password, $employee->emp_password)) {
            return [
                'success' => false,
                'message' => 'Incorrect username or password. Please try again',
                'status' => 401,
                'employee' => null,
            ];
        }

        // Check Status
        if ($employee->emp_status === 'disabled') {
            return [
                'success' => false,
                'message' => 'Account is disabled',
                'status' => 403,
                'employee' => null,
            ];
        }

        if ($employee->emp_delete_status === 'inactive') {
            return [
                'success' => false,
                'message' => 'Account is inactive',
                'status' => 403,
                'employee' => null,
            ];
        }

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
            Auth::login($employee);
            $request->session()->regenerate();

            return [
                'success' => true,
                'message' => 'Login successful',
                'status' => 200,
            ];
        } catch (\Exception $e) {
            Log::error('Login system error', [
                'error' => $e->getMessage(),
                'email' => $employee->emp_email,
            ]);

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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Get the currently authenticated employee.
     *
     * @return Employee|null
     */
    public function getAuthenticatedEmployee(): ?Employee
    {
        if (Auth::check()) {
            return Auth::user();
        }

        return null;
    }
}
