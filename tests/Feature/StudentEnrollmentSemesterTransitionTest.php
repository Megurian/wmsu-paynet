<?php

namespace Tests\Feature;

use App\Http\Controllers\OSASetupController;
use App\Models\College;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEnrollmentSemesterTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected College $college;
    protected Course $course;
    protected YearLevel $yearLevel;
    protected Section $section;
    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->college = College::create([
            'name' => 'Test College',
            'college_code' => 'TEST',
        ]);

        $this->course = Course::create([
            'college_id' => $this->college->id,
            'name' => 'Test Course',
        ]);

        $this->yearLevel = YearLevel::create([
            'college_id' => $this->college->id,
            'name' => '1',
        ]);

        $this->section = Section::create([
            'college_id' => $this->college->id,
            'name' => 'A',
        ]);

        $this->student = Student::create([
            'student_id' => 'STU-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student1@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_ending_a_semester_auto_enrolls_paid_and_cleared_students_in_the_closed_term()
    {
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

        $clearedEnrollment = StudentEnrollment::create([
            'student_id' => $this->student->id,
            'college_id' => $this->college->id,
            'course_id' => $this->course->id,
            'year_level_id' => $this->yearLevel->id,
            'section_id' => $this->section->id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
            'status' => StudentEnrollment::PAID,
            'cleared_for_enrollment' => true,
            'financial_status' => StudentEnrollment::FINANCIAL_PAID,
        ]);

        $paidNotCleared = StudentEnrollment::create([
            'student_id' => $this->student->id,
            'college_id' => $this->college->id,
            'course_id' => $this->course->id,
            'year_level_id' => $this->yearLevel->id,
            'section_id' => $this->section->id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
            'status' => StudentEnrollment::PAID,
            'cleared_for_enrollment' => false,
            'financial_status' => StudentEnrollment::FINANCIAL_PAID,
        ]);

        app(OSASetupController::class)->endSemester($schoolYear->id);

        $this->assertSame(StudentEnrollment::ENROLLED, $clearedEnrollment->refresh()->status);
        $this->assertSame(StudentEnrollment::PAID, $paidNotCleared->refresh()->status);
    }

    public function test_closing_the_final_semester_voids_outstanding_for_payment_validation_records_for_the_academic_year()
    {
        $schoolYear = SchoolYear::create([
            'sy_start' => '2025-08-01',
            'sy_end' => '2026-05-31',
            'is_active' => true,
        ]);

        $firstSemester = Semester::create([
            'school_year_id' => $schoolYear->id,
            'name' => '1st SEMESTER',
            'starts_at' => '2025-08-01',
            'will_end_at' => '2025-12-15',
            'is_active' => false,
        ]);

        $finalSemester = Semester::create([
            'school_year_id' => $schoolYear->id,
            'name' => '2nd SEMESTER',
            'starts_at' => '2026-01-01',
            'will_end_at' => '2026-05-31',
            'is_active' => true,
        ]);

        $pendingFirst = StudentEnrollment::create([
            'student_id' => $this->student->id,
            'college_id' => $this->college->id,
            'course_id' => $this->course->id,
            'year_level_id' => $this->yearLevel->id,
            'section_id' => $this->section->id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $firstSemester->id,
            'status' => StudentEnrollment::FOR_PAYMENT_VALIDATION,
            'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
        ]);

        $pendingFinal = StudentEnrollment::create([
            'student_id' => $this->student->id,
            'college_id' => $this->college->id,
            'course_id' => $this->course->id,
            'year_level_id' => $this->yearLevel->id,
            'section_id' => $this->section->id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $finalSemester->id,
            'status' => StudentEnrollment::FOR_PAYMENT_VALIDATION,
            'financial_status' => StudentEnrollment::FINANCIAL_UNPAID,
        ]);

        $enrolled = StudentEnrollment::create([
            'student_id' => $this->student->id,
            'college_id' => $this->college->id,
            'course_id' => $this->course->id,
            'year_level_id' => $this->yearLevel->id,
            'section_id' => $this->section->id,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $finalSemester->id,
            'status' => StudentEnrollment::ENROLLED,
            'financial_status' => StudentEnrollment::FINANCIAL_PAID,
        ]);

        app(OSASetupController::class)->endSemester($schoolYear->id);

        $this->assertTrue($pendingFirst->refresh()->is_void);
        $this->assertTrue($pendingFinal->refresh()->is_void);
        $this->assertFalse($enrolled->refresh()->is_void);
    }
}
