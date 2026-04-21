<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function showLogin(): RedirectResponse
    {
        return redirect()->route('login');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $student = Student::where('email', $credentials['email'])->first();
        if ($student && empty($student->password)) {
            Password::broker('students')->sendResetLink([
                'email' => $credentials['email'],
            ]);

            return back()->with('status', 'If this email is registered for a student account, a password reset link has been sent.');
        }

        if (auth()->guard('student')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->route('student.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => __('Invalid credentials.'),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
