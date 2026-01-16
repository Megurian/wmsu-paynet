<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OSASetupController;
use App\Http\Controllers\OSACollegeController;
use App\Http\Controllers\CollegeAcademicController;
use App\Http\Controllers\CollegeStudentController;
use App\Http\Controllers\ValidateStudentsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->get('/dashboard', function () {
    $role = Auth::user()->role;

    return match($role) {
        'osa' => redirect()->route('osa.dashboard'),
        'university_org' => redirect()->route('university_org.dashboard'),
        'college' => redirect()->route('college.dashboard'),
        default => abort(403), 
    };
})->name('dashboard');


Route::middleware(['auth', 'role:osa'])->group(function () {
    Route::get('/osa/dashboard', function () {
        return view('osa.dashboard');
    })->name('osa.dashboard');

    //OSA SETUP PAGE ROUTES
    Route::get('/osa/setup', function () {
        return view('osa.setup');
    })->name('osa.setup'); 
    Route::get('/osa/setup', [OSASetupController::class, 'edit'])->name('osa.setup');
    Route::post('/osa/setup', [OSASetupController::class, 'store'])->name('osa.setup.store');
    Route::post('/osa/setup/{id}/add-semester', [OSASetupController::class, 'addSemester'])->name('osa.setup.addSemester');
    //
    //OSA COLLEGE PAGE ROUTES
    Route::get('/osa/college', [OSACollegeController::class, 'index'])->name('osa.college');
    Route::get('/osa/college/create', [OSACollegeController::class, 'create'])->name('osa.college.create');
    Route::post('/osa/college', [OSACollegeController::class, 'store'])->name('osa.college.store');
    Route::get('/osa/college/{id}', [OSACollegeController::class, 'show'])->name('osa.college.details');
    Route::delete('/osa/college/{id}', [OSACollegeController::class, 'destroy'])->name('osa.college.destroy');
});

Route::middleware(['auth', 'role:university_org'])->group(function () {
    Route::get('/university_org/dashboard', function () {
        return view('university_org.dashboard');
    })->name('university_org.dashboard');

    Route::get('/university_org/fees', function () {
        return view('university_org.fees');
    })->name('university_org.fees');

    Route::get('/university_org/remittance', function () {
        return view('university_org.remittance');
    })->name('university_org.remittance');

    Route::get('/university_org/reports', function () {
        return view('university_org.reports');
    })->name('university_org.reports');

    Route::get('/university_org/setup', function () {
        return view('university_org.setup');
    })->name('university_org.setup');
});

Route::middleware(['auth', 'role:college'])->group(function () {
    Route::get('/college/dashboard', function () {
        return view('college.dashboard');
    })->name('college.dashboard');

    Route::get('/college/students', function () {
        return view('college.students');
    })->name('college.students');

    // Academic Structure Management
    Route::get('/college/academics', [CollegeAcademicController::class, 'index'])
        ->name('college.academics');

    Route::post('/college/courses', [CollegeAcademicController::class, 'storeCourse'])
        ->name('college.courses.store');

    Route::post('/college/years', [CollegeAcademicController::class, 'storeYear'])
        ->name('college.years.store');

    Route::post('/college/sections', [CollegeAcademicController::class, 'storeSection'])
        ->name('college.sections.store');
    
    //course,year and section delete
    Route::delete('/college/courses/{id}', [CollegeAcademicController::class, 'destroyCourse'])->name('college.courses.destroy');
    Route::delete('/college/years/{id}', [CollegeAcademicController::class, 'destroyYear'])->name('college.years.destroy');
    Route::delete('/college/sections/{id}', [CollegeAcademicController::class, 'destroySection'])->name('college.sections.destroy');
    Route::get('/college/students', [CollegeStudentController::class, 'index'])->name('college.students');
   
    //students
    Route::post('/college/students', [CollegeStudentController::class, 'store'])->name('college.students.store');
    Route::delete('/college/students/{id}', [CollegeStudentController::class, 'destroy'])->name('college.students.destroy');

    Route::get('students/validate', [ValidateStudentsController::class, 'index'])->name('college.students.validate');
    Route::post('students/validate/{student}', [ValidateStudentsController::class, 'store'])->name('college.students.validate.store');
    Route::post('/college/students/validate/bulk', [ValidateStudentsController::class, 'bulkValidate'])
        ->name('college.students.validate.bulk');

    Route::delete('/college/students/{student}/unvalidate', [CollegeStudentController::class, 'unvalidate']
)   ->name('college.students.unvalidate');

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
