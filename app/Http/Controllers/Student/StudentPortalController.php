<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\PromissoryNote;
use App\Models\Payment;
use App\Models\User;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Notifications\PromissoryNoteSignaturePendingNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
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

    public function showPromissoryNotes()
    {
        $student = auth()->guard('student')->user();

        $promissoryNotes = $student->promissoryNotes()
            ->with([
                'fees.organization',
                'enrollment.course',
                'enrollment.yearLevel',
                'enrollment.section',
                'issuedBy',
            ])
            ->latest('created_at')
            ->get();

        return view('student.promissory_notes', compact('student', 'promissoryNotes'));
    }

    public function downloadPromissoryNoteTemplate(PromissoryNote $note)
    {
        $student = auth()->guard('student')->user();
        $this->authorizePromissoryNote($student, $note);

        $note->load([
            'student',
            'enrollment.course',
            'enrollment.yearLevel',
            'enrollment.section',
            'fees.organization',
            'issuedBy',
        ]);

        try {
            $html = view('student.promissory_notes_template_pdf', compact('student', 'note'))->render();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
            ]);

            $mpdf->WriteHTML($html);
            $pdf = $mpdf->Output('promissory-note-' . $note->id . '.pdf', 'S');
        } catch (\Throwable $e) {
            Log::error('Student promissory note PDF generation failed', [
                'promissory_note_id' => $note->id,
                'student_id' => $student->id,
                'exception' => $e->getMessage(),
            ]);

            abort(500, 'Unable to generate promissory note PDF at this time. Please try again later.');
        }

        return response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="promissory-note-' . $note->id . '.pdf"',
            ]
        );
    }

    public function uploadSignedPromissoryNote(Request $request, PromissoryNote $note)
    {
        $student = auth()->guard('student')->user();
        $this->authorizePromissoryNote($student, $note);

        $validated = $request->validate([
            'signed_note' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120', function ($attribute, $value, $fail) {
                $path = $value->getRealPath();
                if (! $path || ! file_exists($path)) {
                    return $fail('The uploaded file is invalid.');
                }

                $header = file_get_contents($path, false, null, 0, 8);
                if (str_starts_with($header, '%PDF-')
                    || str_starts_with($header, "\xFF\xD8")
                    || $header === "\x89PNG\r\n\x1A\n") {
                    return;
                }

                $fail('The uploaded file must be a valid PDF or image.');
            }],
        ]);

        if (! $note->isPending() || $note->isPendingVerification() || $note->isVoided() || $note->isClosed() || $note->isSignatureOverdue()) {
            return back()->withErrors([
                'signed_note' => 'This promissory note can no longer be signed.',
            ]);
        }

        $file = $validated['signed_note'];
        $fileName = 'signed-pn-' . $note->id . '-' . now()->format('YmdHisv') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $directory = 'promissory_notes/' . $note->id;
        $storedPath = null;

        try {
            $storedPath = $file->storeAs($directory, $fileName, 'local');
            $signedNote = $note->sign($student, $storedPath);

            $this->notifyReviewers($signedNote);

            Log::info('Signed promissory note uploaded', [
                'promissory_note_id' => $note->id,
                'student_id' => $student->id,
                'stored_path' => $storedPath,
                'file_name' => $fileName,
            ]);
        } catch (Throwable $throwable) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            Log::error('Signed promissory note upload failed', [
                'promissory_note_id' => $note->id,
                'student_id' => $student->id,
                'exception' => $throwable->getMessage(),
            ]);

            return back()->withErrors([
                'signed_note' => 'Unable to store signed note at this time. Please try again later.',
            ]);
        }

        return back()->with('status', 'Signed promissory note uploaded. Awaiting coordinator review.');
    }

    private function authorizePromissoryNote($student, PromissoryNote $note): void
    {
        abort_unless((int) $note->student_id === (int) $student->id, 403);
    }

    private function notifyReviewers(PromissoryNote $note): void
    {
        $note->loadMissing(['student', 'enrollment']);

        $reviewers = User::where('role', 'student_coordinator')
            ->where('college_id', $note->enrollment->college_id)
            ->cursor();

        $sent = false;
        foreach ($reviewers as $reviewer) {
            $sent = true;
            $reviewer->notify(new PromissoryNoteSignaturePendingNotification(
                $note->fresh(['student', 'enrollment', 'fees'])
            ));
        }

        if (! $sent && $note->issuedBy) {
            $note->issuedBy->notify(new PromissoryNoteSignaturePendingNotification(
                $note->fresh(['student', 'enrollment', 'fees'])
            ));
        }
    }
}
