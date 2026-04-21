<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\Course;
use App\Models\EmployeeAssignment;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\YearLevel;
use App\Models\Section;

class CollegeAcademicController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $collegeId = $user->college_id;

        return view('college.academics', [
            'courses' => Course::where('college_id', $collegeId)->get(),
            'years' => YearLevel::where('college_id', $collegeId)->get(),
            'sections' => Section::where('college_id', $collegeId)->get(),
        ]);
    }

    public function storeCourse(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'name' => 'required|string|max:1000',
        ]);

        $names = $this->parseCommaSeparatedNames($request);

        $collegeId = $user->college_id;
        $existing = Course::where('college_id', $collegeId)
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        $newNames = $names->reject(fn ($name) => in_array($name, $existing));

        if ($newNames->isEmpty()) {
            return back()->withErrors(['name' => 'No new courses were added. All submitted course names already exist.']);
        }

        foreach ($newNames as $name) {
            Course::create([
                'college_id' => $collegeId,
                'name' => $name,
            ]);
        }

        $status = 'Course(s) added successfully: ' . $newNames->implode(', ');

        if (!empty($existing)) {
            $status .= '. Skipped existing: ' . implode(', ', $existing);
        }

        return back()->with('status', $status);
    }

    public function storeYear(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'name' => 'required|string|max:1000',
        ]);

        $names = $this->parseCommaSeparatedNames($request);

        $collegeId = $user->college_id;
        $existing = YearLevel::where('college_id', $collegeId)
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        $newNames = $names->reject(fn ($name) => in_array($name, $existing));

        if ($newNames->isEmpty()) {
            return back()->withErrors(['name' => 'No new year levels were added. All submitted values already exist.']);
        }

        foreach ($newNames as $name) {
            YearLevel::create([
                'college_id' => $collegeId,
                'name' => $name,
            ]);
        }

        $status = 'Year level(s) added successfully: ' . $newNames->implode(', ');

        if (!empty($existing)) {
            $status .= '. Skipped existing: ' . implode(', ', $existing);
        }

        return back()->with('status', $status);
    }

    public function storeSection(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $request->validate([
            'name' => 'required|string|max:1000',
        ]);

        $names = $this->parseCommaSeparatedNames($request);

        $collegeId = $user->college_id;
        $existing = Section::where('college_id', $collegeId)
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();

        $newNames = $names->reject(fn ($name) => in_array($name, $existing));

        if ($newNames->isEmpty()) {
            return back()->withErrors(['name' => 'No new sections were added. All submitted values already exist.']);
        }

        foreach ($newNames as $name) {
            Section::create([
                'college_id' => $collegeId,
                'name' => $name,
            ]);
        }

        $status = 'Section(s) added successfully: ' . $newNames->implode(', ');

        if (!empty($existing)) {
            $status .= '. Skipped existing: ' . implode(', ', $existing);
        }

        return back()->with('status', $status);
    }

    private function parseCommaSeparatedNames(Request $request)
    {
        $names = collect(explode(',', $request->input('name')))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            throw ValidationException::withMessages(['name' => 'Please provide at least one valid name.']);
        }

        $tooLong = $names->first(fn ($name) => strlen($name) > 255);
        if ($tooLong) {
            throw ValidationException::withMessages(['name' => 'Each name must be 255 characters or fewer.']);
        }

        return $names;
    }

    public function destroyCourse($id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $course = Course::findOrFail($id);
        abort_unless($course->college_id === $user->college_id, 403);

        $studentEnrollmentCount = StudentEnrollment::where('course_id', $course->id)->count();
        $employeeAssignmentCount = EmployeeAssignment::where('course_id', $course->id)->count();
        $userCount = User::where('course_id', $course->id)->count();

        if ($studentEnrollmentCount || $employeeAssignmentCount || $userCount) {
            return back()->withErrors([
                'name' => 'Course cannot be deleted because it is referenced by existing student enrollments or staff assignments.',
            ]);
        }

        $course->delete();

        return back()->with('status', 'Course removed successfully.');
    }

    public function destroyYear($id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $yearLevel = YearLevel::findOrFail($id);
        abort_unless($yearLevel->college_id === $user->college_id, 403);

        $studentEnrollmentCount = StudentEnrollment::where('year_level_id', $yearLevel->id)->count();

        if ($studentEnrollmentCount) {
            return back()->withErrors([
                'name' => 'Year level cannot be deleted because students are assigned to it.',
            ]);
        }

        $yearLevel->delete();

        return back()->with('status', 'Year Level removed successfully.');
    }

    public function destroySection($id)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $section = Section::findOrFail($id);
        abort_unless($section->college_id === $user->college_id, 403);

        $studentEnrollmentCount = StudentEnrollment::where('section_id', $section->id)->count();

        if ($studentEnrollmentCount) {
            return back()->withErrors([
                'name' => 'Section cannot be deleted because students are assigned to it.',
            ]);
        }

        $section->delete();

        return back()->with('status', 'Section removed successfully.');
    }

}
