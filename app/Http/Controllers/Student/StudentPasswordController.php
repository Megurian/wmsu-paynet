<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class StudentPasswordController extends Controller
{
    public function showForgotPassword(): View
    {
        return view('auth.forgot-password', [
            'action' => route('student.password.email'),
            'title' => 'Reset Student Password',
            'description' => 'Enter the email address associated with your student account to receive a password reset link.',
            'backUrl' => route('student.login'),
            'backText' => 'Back to student login',
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('students')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
            'action' => route('student.password.store'),
            'title' => 'Reset Student Password',
            'description' => 'Create a new password for your student account.',
            'backUrl' => route('student.login'),
            'backText' => 'Back to student login',
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('students')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Student $student) use ($request) {
                $student->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($student));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);    }
}
