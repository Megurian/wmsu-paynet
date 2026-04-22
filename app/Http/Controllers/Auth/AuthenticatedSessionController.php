<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
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

        if ($request->session()->has('auth.portal_choice')
            && ! Auth::guard('web')->check()
            && ! auth('student')->check()) {
            return redirect()->route('login.choice');
        }

        $request->session()->regenerate();

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            return $this->redirectWebUser($user);
        }

        if (auth('student')->check()) {
            return redirect()->route('student.dashboard');
        }

        abort(403);
    }

    /**
     * Destroy an authenticated session.
     */
    public function showPortalChoice(Request $request): View
    {
        if (! $request->session()->has('auth.portal_choice')) {
            return view('auth.login');
        }

        return view('auth.portal-choice', [
            'email' => $request->session()->get('auth.portal_choice.email'),
        ]);
    }

    public function choosePortal(Request $request): RedirectResponse
    {
        $request->validate([
            'portal' => ['required', 'in:web,student'],
        ]);

        $choice = $request->session()->pull('auth.portal_choice');
        if (! $choice) {
            return redirect()->route('login');
        }

        try {
            $password = Crypt::decryptString($choice['password']);
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to continue. Please log in again.',
            ]);
        }

        $credentials = [
            'email' => $choice['email'],
            'password' => $password,
        ];

        if (! Auth::guard($request->portal)->attempt($credentials, $choice['remember'] ?? false)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to log in to the selected portal. Please try again.',
            ]);
        }

        $request->session()->regenerate();

        if ($request->portal === 'web') {
            $user = Auth::guard('web')->user();

            return $this->redirectWebUser($user);
        }

        return redirect()->route('student.dashboard');
    }

    private function redirectWebUser($user): RedirectResponse
    {
        if ($user->hasRole('osa')) {
            return redirect()->route('osa.dashboard');
        }

        if ($user->hasRole('university_org')) {
            return redirect()->route('university_org.dashboard');
        }

        if ($user->hasRole('college_org')) {
            return redirect()->route('college_org.dashboard');
        }

        return redirect()->route('college.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
