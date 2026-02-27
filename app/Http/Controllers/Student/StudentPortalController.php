<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Mpdf\Mpdf;

class StudentPortalController extends Controller
{
    public function dashboard()
    {
        $student = auth()->guard('student')->user();

        $activeSchoolYear = SchoolYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        $currentEnrollment = null;
        if ($activeSchoolYear && $activeSemester) {
            $currentEnrollment = $student->enrollments()
                ->with(['course', 'yearLevel', 'section'])
                ->where('school_year_id', $activeSchoolYear->id)
                ->where('semester_id', $activeSemester->id)
                ->first();
        }

        $recentPayments = $student->payments()
            ->with(['fees', 'organization', 'schoolYear', 'semester'])
            ->latest()
            ->take(5)
            ->get();

        return view('student.dashboard', compact(
            'student',
            'activeSchoolYear',
            'activeSemester',
            'currentEnrollment',
            'recentPayments'
        ));
    }

    public function payments()
    {
        $student = auth()->guard('student')->user();

        $payments = $student->payments()
            ->with([
                'fees',
                'organization',
                'enrollment.course',
                'enrollment.yearLevel',
                'enrollment.section',
                'schoolYear',
                'semester',
                'collector',
            ])
            ->latest()
            ->paginate(15);

        return view('student.payments.index', compact('payments'));
    }

    public function downloadReceipt(Payment $payment)
    {
        $studentId = auth()->guard('student')->id();

        if ((int) $payment->student_id !== (int) $studentId) {
            abort(403);
        }

        $payment->load([
            'student',
            'organization',
            'fees',
            'schoolYear',
            'semester',
            'enrollment.course',
            'enrollment.yearLevel',
            'enrollment.section',
            'collector',
        ]);

        $html = view('student.payments.receipt_pdf', compact('payment'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
        ]);

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output('receipt-' . $payment->transaction_id . '.pdf', 'S'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="receipt-' . $payment->transaction_id . '.pdf"',
            ]
        );
    }

    public function profile()
    {
        $student = auth()->guard('student')->user();

        return view('student.profile', compact('student'));
    }

    public function updateProfile(Request $request)
    {
        $student = auth()->guard('student')->user();

        $validated = $request->validate([
            'email' => ['nullable', 'email', Rule::unique('students', 'email')->ignore($student->id)],
            'contact' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $student->email = $validated['email'];
        $student->contact = $validated['contact'];

        if (! empty($validated['password'])) {
            $student->password = $validated['password'];
        }

        $student->save();

        return back()->with('status', 'Profile updated successfully.');
    }
}
