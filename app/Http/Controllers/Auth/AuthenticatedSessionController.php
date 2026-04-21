<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $student = Student::where('email', $request->email)->first();

        if ($student && empty($student->password)) {
            Password::broker('students')->sendResetLink([
                'email' => $request->email,
            ]);

            return back()->with('status', 'If this email is registered for a student account, a password reset link has been sent.');
        }

        $request->authenticate();

        $request->session()->regenerate();

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            return match ($user->role) {
                'osa' => redirect()->route('osa.dashboard'),
                'university_org' => redirect()->route('university_org.dashboard'),
                'college_org' => redirect()->route('college_org.dashboard'),
                default => redirect()->route('college.dashboard'),
            };
        }

        if (auth('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        abort(403);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
