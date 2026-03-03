<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OSASetupController;
use App\Http\Controllers\OSACollegeController;
use App\Http\Controllers\OSAOrganizationsController;
use App\Http\Controllers\CollegeAcademicController;
use App\Http\Controllers\CollegeStudentController;
use App\Http\Controllers\CollegeHistoryController;
use App\Http\Controllers\ValidateStudentsController;
use App\Http\Controllers\UniversityOrgFeesController;
use App\Http\Controllers\UniversityOrgReportsController;
use App\Http\Controllers\UniversityOrgOfficesController;
use App\Http\Controllers\CollegeUserController;
use App\Http\Middleware\CheckActiveSchoolYear;
use App\Http\Controllers\AdviserStudentUploadController;
use App\Http\Controllers\CollegeFeeController;
use App\Http\Controllers\CollegeFeeApprovalController;
use App\Http\Controllers\TreasurerCashieringController;
use App\Http\Controllers\LocalOrgsController;
use App\Http\Controllers\CollegeOrgApprovalController;
use App\Http\Controllers\OrganizationPaymentController;
use App\Http\Controllers\DocumentController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

Route::get('/test-route', function () {
    return 'Laravel route works!';
});


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
        'treasurer' => redirect()->route('treasurer.cashiering'),
        default => abort(403), 
    };
})->name('dashboard');

Route::middleware(['auth', 'role:osa'])->group(function () {
    Route::get('/osa/setup', [OSASetupController::class, 'edit'])->name('osa.setup');
    Route::post('/osa/setup', [OSASetupController::class, 'store'])->name('osa.setup.store');
    Route::post('/osa/setup/{id}/add-semester', [OSASetupController::class, 'addSemester'])->name('osa.setup.addSemester');
    Route::post('/osa/setup/{schoolYear}/end-semester', [OSASetupController::class, 'endSemester'])->name('osa.setup.end-semester');
});

Route::middleware(['auth', 'role:osa', CheckActiveSchoolYear::class])->group(function () {
    Route::get('/osa/dashboard', function () {
        return view('osa.dashboard');
    })->name('osa.dashboard');
    Route::get('/osa/fees', [App\Http\Controllers\OSAFeesController::class, 'index'])->name('osa.fees');
    Route::get('/osa/fees/create', [App\Http\Controllers\OSAFeesController::class, 'create'])->name('osa.fees.create');
    Route::post('/osa/fees', [App\Http\Controllers\OSAFeesController::class, 'store'])->name('osa.fees.store');
    Route::get('/osa/fees/{fee}', [App\Http\Controllers\OSAFeesController::class, 'show'])->name('osa.fees.show');
    Route::post('/osa/fees/{fee}/approve', [App\Http\Controllers\OSAFeesController::class, 'approve'])->name('osa.fees.approve');
    Route::post('/osa/fees/{fee}/disable', [App\Http\Controllers\OSAFeesController::class, 'disable'])->name('osa.fees.disable');

    // Appeals actions (OSA)
    Route::post('/osa/appeals/{appeal}/accept', [App\Http\Controllers\OSAFeesController::class, 'acceptAppeal'])->name('osa.appeals.accept');
    Route::post('/osa/appeals/{appeal}/reject', [App\Http\Controllers\OSAFeesController::class, 'rejectAppeal'])->name('osa.appeals.reject');



    Route::get('/osa/organizations', [OSAOrganizationsController::class, 'index'])->name('osa.organizations');
    Route::get('/osa/organizations/create', [OSAOrganizationsController::class, 'create'])->name('osa.organizations.create');
    Route::post('/osa/organizations', [OSAOrganizationsController::class, 'store'])->name('osa.organizations.store');
    Route::get('/osa/organizations/{id}', [OSAOrganizationsController::class, 'show'])->name('osa.organizations.show');
    Route::delete('/osa/organizations/{id}', [OSAOrganizationsController::class, 'destroy'])->name('osa.organizations.destroy');
    Route::post('/osa/organizations/{id}/toggle-osa-inheritance', [OSAOrganizationsController::class, 'toggleOsaInheritance'])->name('osa.organizations.toggle-osa-inheritance');
    
    //OSA COLLEGE PAGE ROUTES
    Route::get('/osa/college', [OSACollegeController::class, 'index'])->name('osa.college');
    Route::get('/osa/college/create', [OSACollegeController::class, 'create'])->name('osa.college.create');
    Route::post('/osa/college', [OSACollegeController::class, 'store'])->name('osa.college.store');

    // AJAX validation endpoints (live uniqueness checks)
    Route::post('/osa/college/check-code', [OSACollegeController::class, 'checkCode'])->name('osa.college.checkCode');
    Route::post('/osa/college/check-email', [OSACollegeController::class, 'checkEmail'])->name('osa.college.checkEmail');

    // AJAX validation endpoints for organizations (live uniqueness checks)
    Route::post('/osa/organizations/check-code', [OSAOrganizationsController::class, 'checkCode'])->name('osa.organizations.checkCode');
    Route::post('/osa/organizations/check-email', [OSAOrganizationsController::class, 'checkEmail'])->name('osa.organizations.checkEmail');

    Route::get('/osa/college/{id}', [OSACollegeController::class, 'show'])->name('osa.college.details');
    Route::delete('/osa/college/{id}', [OSACollegeController::class, 'destroy'])->name('osa.college.destroy');
});

Route::middleware(['auth', 'role:university_org'])->group(function () {
    Route::get('/university_org/dashboard', function () {
        return view('university_org.dashboard');
    })->name('university_org.dashboard');

    Route::get('/university_org/offices', function () {
        return redirect()->route('university_org.offices.index');
    })->name('university_org.offices');

    Route::get('/university_org/remittance', function () {
        return view('university_org.remittance');
    })->name('university_org.remittance');

    Route::get('/university_org/reports', [UniversityOrgReportsController::class, 'paymentCollectionReport'])
    ->name('university_org.reports');

    Route::get('/university-org/fees', [UniversityOrgFeesController::class, 'index'])->name('university_org.fees');
    Route::get('/university-org/fees/create', [UniversityOrgFeesController::class, 'create'])->name('university_org.fees.create');
    Route::post('/university-org/fees', [UniversityOrgFeesController::class, 'store'])->name('university_org.fees.store');
    Route::get('/university-org/fees/{fee}', [UniversityOrgFeesController::class, 'show'])->name('university_org.fees.show');
    Route::get('/university-org/fees/{fee}/edit', [UniversityOrgFeesController::class, 'edit'])->name('university_org.fees.edit');
    Route::put('/university-org/fees/{fee}', [UniversityOrgFeesController::class, 'update'])->name('university_org.fees.update');
    Route::delete('/university-org/fees/{fee}', [UniversityOrgFeesController::class, 'destroy'])->name('university_org.fees.destroy');
    Route::post('/university-org/fees/{fee}/appeal', [UniversityOrgFeesController::class, 'submitAppeal'])->name('university_org.fees.appeal');

    Route::get('/university-org/offices', [UniversityOrgOfficesController::class, 'index'])->name('university_org.offices.index');
    Route::get('/university-org/offices/create', [UniversityOrgOfficesController::class, 'create'])->name('university_org.offices.create');
    Route::post('/university-org/offices', [UniversityOrgOfficesController::class, 'store'])->name('university_org.offices.store');

    // AJAX validation endpoints for organizations (live uniqueness checks)
    Route::post('/university-org/organizations/check-code', [UniversityOrgOfficesController::class, 'checkCode'])->name('university_org.organizations.checkCode');
    Route::post('/university-org/organizations/check-email', [UniversityOrgOfficesController::class, 'checkEmail'])->name('university_org.organizations.checkEmail');

    // Documents routes
    Route::get('/university-org/documents', [DocumentController::class, 'index'])->name('university_org.documents.index')->defaults('role', 'university_org');
    Route::post('/university-org/documents', [DocumentController::class, 'store'])->name('university_org.documents.store')->defaults('role', 'university_org');
    Route::get('/university-org/documents/{document}/preview', [DocumentController::class, 'preview'])->name('university_org.documents.preview');
    Route::delete('/university-org/documents/{document}', [DocumentController::class, 'destroy'])->name('university_org.documents.destroy');

});

Route::middleware(['auth', 'role:college_org'])->group(function () {
    Route::get('/college_org/dashboard', function () {
        return view('college_org.dashboard');
    })->name('college_org.dashboard');

    // Use controller so we can display inherited fees from the mother organization
    Route::get('/college_org/fees', [App\Http\Controllers\CollegeOrgFeesController::class, 'index'])->name('college_org.fees');

    // College org fee management (create/store/show/edit/update/destroy/appeal)
    Route::get('/college_org/fees/create', [App\Http\Controllers\CollegeOrgFeesController::class, 'create'])->name('college_org.fees.create');
    Route::post('/college_org/fees', [App\Http\Controllers\CollegeOrgFeesController::class, 'store'])->name('college_org.fees.store');
    Route::get('/college_org/fees/{fee}', [App\Http\Controllers\CollegeOrgFeesController::class, 'show'])->name('college_org.fees.show');
    Route::get('/college_org/fees/{fee}/edit', [App\Http\Controllers\CollegeOrgFeesController::class, 'edit'])->name('college_org.fees.edit');
    Route::put('/college_org/fees/{fee}', [App\Http\Controllers\CollegeOrgFeesController::class, 'update'])->name('college_org.fees.update');
    Route::delete('/college_org/fees/{fee}', [App\Http\Controllers\CollegeOrgFeesController::class, 'destroy'])->name('college_org.fees.destroy');
    Route::post('/college_org/fees/{fee}/appeal', [App\Http\Controllers\CollegeOrgFeesController::class, 'submitAppeal'])->name('college_org.fees.appeal');

    Route::get('/college_org/payment', function () {
        return view('college_org.payment');
    })->name('college_org.payment');
    Route::get(
        '/college_org/records',
        [OrganizationPaymentController::class, 'records']
    )->name('college_org.records');
    // routes/web.php
    Route::get('college_org/search-students', [OrganizationPaymentController::class, 'searchStudents'])->name('college_org.search_students');
    // Route::get('/college/students/search', [OrganizationPaymentController::class, 'searchStudents'])
    //  ->name('college.students.search');
Route::get('/college-org/generate-report', 
    [OrganizationPaymentController::class, 'generateReport']
)->name('college_org.generate_report');
    Route::get('/college/students/search', [OrganizationPaymentController::class,'searchStudents']);
   Route::get('/college_org/students/{student}/fees',
    [OrganizationPaymentController::class,'getStudentFees'])
    ->name('college_org.students.fees');
    Route::post('/college_org/payment/collect', [OrganizationPaymentController::class,'collectPayment']);
    
    Route::get('/college_org/documents', [DocumentController::class, 'index'])->name('college_org.documents.index')->defaults('role', 'college_org');
    Route::post('/college_org/documents', [DocumentController::class, 'store'])->name('college_org.documents.store')->defaults('role', 'college_org');
    Route::get('/college_org/documents/{document}/preview', [DocumentController::class, 'preview'])->name('college_org.documents.preview');
    Route::delete('/college_org/documents/{document}', [DocumentController::class, 'destroy'])->name('college_org.documents.destroy');
});

Route::middleware(['auth','role:adviser'])->group(function(){
    Route::get('/college/students/my-upload', [AdviserStudentUploadController::class, 'index'])
        ->name('college.students.my-upload');

    Route::post('/college/students/my-upload', [AdviserStudentUploadController::class, 'store'])
        ->name('college.students.my-upload.store');

    Route::post('/college/students/{student}/readd', [AdviserStudentUploadController::class, 'reAddOldStudent'])
    ->name('college.students.readd');

    Route::post('/college/students/readd/bulk', [AdviserStudentUploadController::class, 'reAddBulk'])->name('college.students.readd.bulk');
});

Route::middleware(['auth','role:college'])->group(function () {
    Route::get('/college/fees/approval', [CollegeFeeApprovalController::class, 'index'])
        ->name('college.fees.approval');

    // restrict {fee} to numbers so literal segments (e.g. "create") don't match
    Route::get('/college/fees/{fee}', [CollegeFeeApprovalController::class, 'show'])
        ->whereNumber('fee')
        ->name('college.fees.show');

    Route::post('/college/fees/{fee}/approve', [CollegeFeeApprovalController::class, 'approve'])
        ->whereNumber('fee')
        ->name('college.fees.approve');

    Route::post('/college/fees/{fee}/reject', [CollegeFeeApprovalController::class, 'reject'])
        ->whereNumber('fee')
        ->name('college.fees.reject');
});

Route::middleware(['auth', 'role:treasurer,college,student_coordinator,adviser,assessor'])->group(function () {
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
    Route::get('/college/students/{student}', [CollegeStudentController::class, 'show'])
    ->name('college.students.show');
    Route::put('/college/students/{student}', [CollegeStudentController::class, 'update'])
    ->name('college.students.update');


    Route::delete('/college/students/{student}/unvalidate', [CollegeStudentController::class, 'unvalidate']
    )  ->name('college.students.unvalidate');

    Route::get('/college/history', [CollegeHistoryController::class, 'history'])->name('college.history');
    Route::post('/college/users/{user}/assign-course', [CollegeUserController::class, 'assignCourse'])
    ->name('college.users.assign-course');
    Route::get('/college/users', [CollegeUserController::class, 'index'])->name('college.users.index');
    Route::get('/college/users/create', [CollegeUserController::class, 'create'])->name('college.users.create');
    Route::post('/college/users', [CollegeUserController::class, 'store'])->name('college.users.store');
    Route::delete('/college/users/{user}', [CollegeUserController::class, 'destroy'])->name('college.users.destroy');
     Route::put('info/logo', [CollegeUserController::class, 'updateCollegeLogo'])->name('college.info.updateLogo');
    Route::put('info/name', [CollegeUserController::class, 'updateCollegeName'])->name('college.info.updateName');
    Route::get('/college/students/import/template', function () {
        \App\Services\StudentTemplateGenerator::generateIfNotExists();
        
        $disk = Storage::disk('local');
        $file = $disk->path('templates/student_template.csv');

        // Fallback: move from old location if it exists
        if (! file_exists($file)) {
            $old = $disk->path('private/templates/student_template.csv');
            if (file_exists($old)) {
                $disk->move('private/templates/student_template.csv', 'templates/student_template.csv');
                $file = $disk->path('templates/student_template.csv');
            }
        }

        if (! file_exists($file)) {
            abort(404, 'Template not found.');
        }

        return response()->download($file, 'student_template.csv');
    })->name('college.students.import.template');
    Route::post('college/students/import', [ValidateStudentsController::class, 'import'])->name('college.students.import');
    Route::post('college/students/import/preview', [ValidateStudentsController::class, 'previewImport'])->name('college.students.import.preview');
    Route::get('/college/local_organizations/approvals', [CollegeOrgApprovalController::class, 'index'])
        ->name('college.local_organizations.approvals');

    Route::post('/college/local_organizations/{organization}/approve', [CollegeOrgApprovalController::class, 'approve'])
        ->name('college.local_organizations.approve');

    Route::post('/college/local_organizations/{organization}/reject', [CollegeOrgApprovalController::class, 'reject'])
        ->name('college.local_organizations.reject');
        Route::post('/college/students/{student}/clear-for-enrollment', [ValidateStudentsController::class, 'clearForEnrollment'])
    ->name('college.students.clear-for-enrollment');
    
    Route::get('/college/students/{student}/history', [CollegeHistoryController::class, 'showStudentHistory'])
        ->name('college.students.history');

    Route::get('/college/history/fees', [CollegeHistoryController::class, 'getFeesByOrg']);
       Route::get('/college/history/report', [CollegeHistoryController::class, 'generateReport'])
    ->name('college.history.report');
});

    Route::middleware(['auth','role:assessor,student_coordinator'])->group(function(){
    Route::get('students/validate', [ValidateStudentsController::class, 'index'])->name('college.students.validate');
    Route::post('students/validate/{student}', [ValidateStudentsController::class, 'store'])->name('college.students.validate.store');
    Route::post('/college/students/validate/bulk', [ValidateStudentsController::class, 'bulkValidate'])
        ->name('college.students.validate.bulk');
     Route::get('/college/students/{student}/fees', [ValidateStudentsController::class, 'getFeesForStudent']);
});



Route::middleware(['auth', 'role:treasurer'])->group(function () {
     Route::get('/college/cashiering', [TreasurerCashieringController::class, 'index'])->name('treasurer.cashiering');
    Route::get('/treasurer/cashiering/search', [TreasurerCashieringController::class, 'searchAdvisedStudents']);
    Route::get('/treasurer/cashiering/student/{student}', [TreasurerCashieringController::class, 'getStudentDetails']);
    Route::post('/treasurer/cashiering/collect', [TreasurerCashieringController::class, 'collectPayment']);
    // receipt download disabled for now
    // Route::get('/treasurer/cashiering/receipt/pdf/{payment}', [TreasurerCashieringController::class, 'downloadReceipt'])
    //    ->name('cashiering.receipt.pdf');
});

Route::middleware(['auth','role:student_coordinator'])->group(function(){
    Route::post(
        '/college/students/{student}/mark-paid',
        [ValidateStudentsController::class, 'markPaid']
    )->name('college.students.markPaid');

    Route::get('/college/fees', [CollegeFeeController::class, 'index'])
        ->name('college.fees');

    Route::get('/college/fees/create', [CollegeFeeController::class, 'create'])
        ->name('college.fees.create');

    Route::post('/college/fees', [CollegeFeeController::class, 'store'])
        ->name('college.fees.store');

    Route::get('/college/local_organizations', [LocalOrgsController::class, 'index'])
        ->name('college.local_organizations');
    Route::get('/college/local_organizations/create', [LocalOrgsController::class, 'create'])
        ->name('college.local_organizations.create');

    Route::post('/college/local_organizations', [LocalOrgsController::class, 'store'])
        ->name('college.local_organizations.store');

        Route::get('college/local_organizations/{org}', [LocalOrgsController::class, 'show'])->name('college.local_organizations.show');
Route::delete('/college/local_organizations/{org}/cancel', [LocalOrgsController::class, 'cancelSubmission'])->name('college.local_organizations.cancel_submission');
});



Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
