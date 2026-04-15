<?php

use App\Exceptions\PromissoryNotePartialAllocationException;
use App\Models\College;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\PromissoryNote;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Course;
use App\Models\Section;
use App\Models\YearLevel;
use App\Models\User;
use App\Http\Controllers\ValidateStudentsController;
use App\Services\PromissoryNoteDelinquencyService;
use App\Services\PromissoryNoteSettlementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

test('promissory note settlement rejects underpayment for selected fees', function () {
    $college = College::create(['name' => 'Test College', 'college_code' => 'TEST']);
    $user = User::create([
        'first_name' => 'Issuer',
        'last_name' => 'User',
        'email' => 'issuer1@example.com',
        'password' => 'password',
        'college_id' => $college->id,
    ]);

    $schoolYear = SchoolYear::create([
        'sy_start' => '2025-08-01',
        'sy_end' => '2026-05-31',
        'is_active' => true,
    ]);

    $semester = Semester::create([
        'school_year_id' => $schoolYear->id,
        'name' => '1st SEMESTER',
        'starts_at' => '2025-08-01',
        'will_end_at' => '2025-12-15',
        'is_active' => true,
    ]);

    $student = Student::create([
        'student_id' => 'STU-001',
        'first_name' => 'Test',
        'last_name' => 'Student',
        'email' => 'student1@example.com',
        'password' => 'password',
    ]);

    $course = Course::create(['college_id' => $college->id, 'name' => 'Test Course']);
    $yearLevel = YearLevel::create(['college_id' => $college->id, 'name' => '1']);
    $section = Section::create(['college_id' => $college->id, 'name' => 'A']);

    $enrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'college_id' => $college->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $section->id,
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
        'status' => StudentEnrollment::ENROLLED,
    ]);

    $note = PromissoryNote::create([
        'student_id' => $student->id,
        'enrollment_id' => $enrollment->id,
        'issued_by' => $user->id,
        'status' => PromissoryNote::STATUS_ACTIVE,
        'original_amount' => 1000,
        'remaining_balance' => 1000,
        'due_date' => Carbon::tomorrow()->toDateString(),
        'signature_deadline' => Carbon::now()->addDay()->toDateTimeString(),
    ]);

    $feeA = Fee::create([
        'organization_id' => null,
        'user_id' => $user->id,
        'fee_name' => 'Fee A',
        'purpose' => 'Deferred fee',
        'description' => 'Test fee A',
        'amount' => 500,
        'remittance_percent' => 100,
        'requirement_level' => 'mandatory',
        'fee_scope' => 'college',
        'college_id' => $college->id,
        'status' => 'approved',
        'approval_level' => 'osa',
    ]);

    $feeB = Fee::create([
        'organization_id' => null,
        'user_id' => $user->id,
        'fee_name' => 'Fee B',
        'purpose' => 'Deferred fee',
        'description' => 'Test fee B',
        'amount' => 500,
        'remittance_percent' => 100,
        'requirement_level' => 'mandatory',
        'fee_scope' => 'college',
        'college_id' => $college->id,
        'status' => 'approved',
        'approval_level' => 'osa',
    ]);

    DB::table('promissory_note_fees')->insert([
        ['promissory_note_id' => $note->id, 'fee_id' => $feeA->id, 'amount_deferred' => 500, 'created_at' => now(), 'updated_at' => now()],
        ['promissory_note_id' => $note->id, 'fee_id' => $feeB->id, 'amount_deferred' => 500, 'created_at' => now(), 'updated_at' => now()],
    ]);

    Auth::login($user);

    $service = new PromissoryNoteSettlementService();

    expect(fn () => $service->settlePayment(
        $note,
        400,
        [$feeA->id, $feeB->id],
        $user,
        false
    ))->toThrow(PromissoryNotePartialAllocationException::class);
});

test('promissory note settlement transaction sequence is stable for treasury payments', function () {
    $college = College::create(['name' => 'Test College', 'college_code' => 'TEST']);
    $user = User::create([
        'first_name' => 'Issuer',
        'last_name' => 'User',
        'email' => 'issuer2@example.com',
        'password' => 'password',
        'college_id' => $college->id,
    ]);

    $schoolYear = SchoolYear::create([
        'sy_start' => '2025-08-01',
        'sy_end' => '2026-05-31',
        'is_active' => true,
    ]);

    $semester = Semester::create([
        'school_year_id' => $schoolYear->id,
        'name' => '1st SEMESTER',
        'starts_at' => '2025-08-01',
        'will_end_at' => '2025-12-15',
        'is_active' => true,
    ]);

    $student = Student::create([
        'student_id' => 'STU-002',
        'first_name' => 'Sequence',
        'last_name' => 'User',
        'email' => 'student2@example.com',
        'password' => 'password',
    ]);

    $course = Course::create(['college_id' => $college->id, 'name' => 'Sequence Course']);
    $yearLevel = YearLevel::create(['college_id' => $college->id, 'name' => '2']);
    $section = Section::create(['college_id' => $college->id, 'name' => 'B']);

    $enrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'college_id' => $college->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $section->id,
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
        'status' => StudentEnrollment::ENROLLED,
    ]);

    $note = PromissoryNote::create([
        'student_id' => $student->id,
        'enrollment_id' => $enrollment->id,
        'issued_by' => $user->id,
        'status' => PromissoryNote::STATUS_ACTIVE,
        'original_amount' => 1000,
        'remaining_balance' => 1000,
        'due_date' => Carbon::tomorrow()->toDateString(),
        'signature_deadline' => Carbon::now()->addDay()->toDateTimeString(),
    ]);

    $fee = Fee::create([
        'organization_id' => null,
        'user_id' => $user->id,
        'fee_name' => 'Fee Sequence',
        'purpose' => 'Deferred fee',
        'description' => 'Test fee sequence',
        'amount' => 500,
        'remittance_percent' => 100,
        'requirement_level' => 'mandatory',
        'fee_scope' => 'college',
        'college_id' => $college->id,
        'status' => 'approved',
        'approval_level' => 'osa',
    ]);

    DB::table('promissory_note_fees')->insert([
        'promissory_note_id' => $note->id,
        'fee_id' => $fee->id,
        'amount_deferred' => 500,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Payment::create([
        'student_id' => $student->id,
        'enrollment_id' => $enrollment->id,
        'organization_id' => null,
        'payment_type' => 'PROMISSORY',
        'promissory_note_id' => $note->id,
        'amount_due' => 500,
        'cash_received' => 500,
        'change' => 0,
        'collected_by' => $user->id,
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
        'transaction_id' => 'TEST-TRES-' . now()->format('Ymd') . '-0001-AAA',
    ]);

    Auth::login($user);

    $service = new PromissoryNoteSettlementService();
    $result = $service->settlePayment(
        $note,
        500,
        [$fee->id],
        $user,
        false
    );

    expect($result['transaction_id'])->toContain('-0002-');
});

test('delinquency service promotes defaulted note to bad debt based on note school year end', function () {
    $college = College::create(['name' => 'Test College', 'college_code' => 'TEST']);
    $user = User::create([
        'first_name' => 'Issuer',
        'last_name' => 'User',
        'email' => 'issuer3@example.com',
        'password' => 'password',
        'college_id' => $college->id,
    ]);

    $schoolYear = SchoolYear::create([
        'sy_start' => '2024-08-01',
        'sy_end' => Carbon::yesterday()->toDateString(),
        'is_active' => false,
    ]);

    $semester = Semester::create([
        'school_year_id' => $schoolYear->id,
        'name' => '1st SEMESTER',
        'starts_at' => '2024-08-01',
        'will_end_at' => '2024-12-15',
        'is_active' => false,
    ]);

    $student = Student::create([
        'student_id' => 'STU-003',
        'first_name' => 'Delinquent',
        'last_name' => 'Student',
        'email' => 'student3@example.com',
        'password' => 'password',
    ]);

    $course = Course::create(['college_id' => $college->id, 'name' => 'Delinquent Course']);
    $yearLevel = YearLevel::create(['college_id' => $college->id, 'name' => '3']);
    $section = Section::create(['college_id' => $college->id, 'name' => 'C']);

    $enrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'college_id' => $college->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $section->id,
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
        'status' => StudentEnrollment::ENROLLED,
    ]);

    $note = PromissoryNote::create([
        'student_id' => $student->id,
        'enrollment_id' => $enrollment->id,
        'issued_by' => $user->id,
        'status' => PromissoryNote::STATUS_DEFAULT,
        'original_amount' => 1000,
        'remaining_balance' => 1000,
        'due_date' => Carbon::parse($schoolYear->sy_end)->subDays(30)->toDateString(),
        'signature_deadline' => Carbon::now()->subDays(120)->toDateTimeString(),
        'default_date' => Carbon::yesterday()->subDay()->toDateTimeString(),
    ]);

    $service = new PromissoryNoteDelinquencyService();
    $result = $service->evaluateDelinquency($note, Carbon::today(), ['bad_debt_ready' => true]);

    expect($result['transitioned'])->toBeTrue();
    expect($result['to_status'])->toBe(PromissoryNote::STATUS_BAD_DEBT);
    expect($note->fresh()->status)->toBe(PromissoryNote::STATUS_BAD_DEBT);
});

test('validate students uses semester effective end date for promissory note ceilings', function () {
    $schoolYear = SchoolYear::create([
        'sy_start' => '2025-08-01',
        'sy_end' => '2026-05-31',
        'is_active' => true,
    ]);

    $semester = Semester::create([
        'school_year_id' => $schoolYear->id,
        'name' => '1st SEMESTER',
        'starts_at' => '2025-08-01',
        'will_end_at' => '2025-12-15',
        'is_active' => true,
    ]);

    $college = College::create(['name' => 'Test College', 'college_code' => 'TEST']);
    $user = User::create([
        'first_name' => 'Issuer',
        'last_name' => 'User',
        'email' => 'issuer4@example.com',
        'password' => 'password',
        'college_id' => $college->id,
    ]);

    $student = Student::create([
        'student_id' => 'STU-004',
        'first_name' => 'Validate',
        'last_name' => 'Student',
        'email' => 'student4@example.com',
        'password' => 'password',
    ]);

    $course = Course::create(['college_id' => $college->id, 'name' => 'Validate Course']);
    $yearLevel = YearLevel::create(['college_id' => $college->id, 'name' => '4']);
    $section = Section::create(['college_id' => $college->id, 'name' => 'D']);

    $enrollment = StudentEnrollment::create([
        'student_id' => $student->id,
        'college_id' => $college->id,
        'course_id' => $course->id,
        'year_level_id' => $yearLevel->id,
        'section_id' => $section->id,
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
        'status' => StudentEnrollment::ENROLLED,
    ]);

    $controller = new ValidateStudentsController();
    $method = new ReflectionMethod($controller, 'resolvePromissoryNoteDueDateCeiling');
    $method->setAccessible(true);

    $result = $method->invoke($controller, $enrollment);

    expect($result?->toDateString())->toBe($semester->effectiveEndDate()->toDateString());
});
