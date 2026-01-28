<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OSASetupController;
use App\Http\Controllers\OSACollegeController;
use App\Http\Controllers\OSAOrganizationsController;
use App\Http\Controllers\CollegeAcademicController;
use App\Http\Controllers\CollegeStudentController;
use App\Http\Controllers\CollegeHistoryController;
use App\Http\Controllers\ValidateStudentsController;
use App\Http\Controllers\OrganizationPaymentController;
use App\Http\Controllers\UniversityOrgFeesController;
use App\Http\Controllers\UniversityOrgOfficesController;
use App\Http\Controllers\CollegeUserController;
use App\Http\Middleware\CheckActiveSchoolYear;
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
        'college_org' => redirect()->route('college_org.dashboard'),
        'college' => redirect()->route('college.dashboard'),
        default => abort(403), 
    };
})->name('dashboard');

Route::middleware(['auth', 'role:osa'])->group(function () {
    Route::get('/osa/setup', function () {
        return view('osa.setup');
    })->name('osa.setup'); 
    Route::get('/osa/setup', [OSASetupController::class, 'edit'])->name('osa.setup');
    Route::post('/osa/setup', [OSASetupController::class, 'store'])->name('osa.setup.store');
    Route::post('/osa/setup/{id}/add-semester', [OSASetupController::class, 'addSemester'])->name('osa.setup.addSemester');
    Route::post('/osa/setup/{schoolYear}/end-semester', [OSASetupController::class, 'endSemester'])->name('osa.setup.end-semester');
});

Route::middleware(['auth', 'role:osa', CheckActiveSchoolYear::class])->group(function () {
    Route::get('/osa/dashboard', function () {
        return view('osa.dashboard');
    })->name('osa.dashboard');
    Route::get('/osa/fees', function () {
        return view('osa.fees');
    })->name('osa.fees');
    Route::get('/osa/organizations', function () {
        return view('osa.organizations');
    })->name('osa.organizations');
    Route::get('/osa/organizations', [OSAOrganizationsController::class, 'index'])->name('osa.organizations');
    Route::get('/osa/organizations/create', [OSAOrganizationsController::class, 'create'])->name('osa.organizations.create');
    Route::post('/osa/organizations', [OSAOrganizationsController::class, 'store'])->name('osa.organizations.store');
    Route::get('/osa/organizations/{id}', [OSAOrganizationsController::class, 'show'])->name('osa.organizations.show');
    Route::delete('/osa/organizations/{id}', [OSAOrganizationsController::class, 'destroy'])->name('osa.organizations.destroy');
    // //OSA SETUP PAGE ROUTES
    // Route::get('/osa/setup', function () {
    //     return view('osa.setup');
    // })->name('osa.setup'); 
    // Route::get('/osa/setup', [OSASetupController::class, 'edit'])->name('osa.setup');
    // Route::post('/osa/setup', [OSASetupController::class, 'store'])->name('osa.setup.store');
    // Route::post('/osa/setup/{id}/add-semester', [OSASetupController::class, 'addSemester'])->name('osa.setup.addSemester');
    
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

    Route::get('/university_org/offices', function () {
        return view('university_org.offices');
    })->name('university_org.offices');

    Route::get('/university_org/remittance', function () {
        return view('university_org.remittance');
    })->name('university_org.remittance');

    Route::get('/university_org/reports', function () {
        return view('university_org.reports');
    })->name('university_org.reports');

    Route::get('/university-org/fees', [UniversityOrgFeesController::class, 'index'])->name('university_org.fees');
    Route::get('/university-org/fees/create', [UniversityOrgFeesController::class, 'create'])->name('university_org.fees.create');
    Route::post('/university-org/fees', [UniversityOrgFeesController::class, 'store'])->name('university_org.fees.store');

    Route::get('/university-org/offices', [UniversityOrgOfficesController::class, 'index'])->name('university_org.offices.index');
    Route::get('/university-org/offices/create', [UniversityOrgOfficesController::class, 'create'])->name('university_org.offices.create');
    Route::post('/university-org/offices', [UniversityOrgOfficesController::class, 'store'])->name('university_org.offices.store');
});

Route::middleware(['auth', 'role:college_org'])->group(function () {
    Route::get('/college_org/dashboard', function () {
        return view('college_org.dashboard');
    })->name('college_org.dashboard');
    Route::get('/college_org/fees', function () {
        return view('college_org.fees');
    })->name('college_org.fees');
    Route::get('/college_org/payment', function () {
        return view('college_org.payment');
    })->name('college_org.payment');
     Route::get('/college_org/records', function () {
        return view('college_org.records');
    })->name('college_org.records');
   Route::get('/college/students/search', [OrganizationPaymentController::class, 'searchStudents'])
     ->name('college.students.search');
});

Route::middleware(['auth', 'role:college,student_coordinator,adviser'])->group(function () {
    Route::get('/college/dashboard', function () {
        return view('college.dashboard');
    })->name('college.dashboard');

    Route::get('/college/students', function () {
        return view('college.students');
    })->name('college.students');

    Route::get('/college/history', function () {
        return view('college.history');
    })->name('college.history');

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
    )  ->name('college.students.unvalidate');

    Route::get('/college/history', [CollegeHistoryController::class, 'history'])->name('college.history');

    Route::get('/college/users', [CollegeUserController::class, 'index'])->name('college.users.index');
    Route::get('/college/users/create', [CollegeUserController::class, 'create'])->name('college.users.create');
    Route::post('/college/users', [CollegeUserController::class, 'store'])->name('college.users.store');
    Route::delete('/college/users/{user}', [CollegeUserController::class, 'destroy'])->name('college.users.destroy');

    Route::get('/college/students/import/template', function () {
        return response()->download(
            storage_path('app/templates/student_import_template.xlsx'),
            'student_import_template.xlsx'
        );
    })->name('college.students.import.template');
    Route::post('college/students/import', [ValidateStudentsController::class, 'import'])->name('college.students.import');

});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
