<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('student.auth.login');
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'student_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (auth()->guard('student')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->route('student.dashboard');
        }

        throw ValidationException::withMessages([
            'student_id' => __('These credentials do not match our records.'),
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
