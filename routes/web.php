<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeachingAssignmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\OwnerSchoolController;
use App\Http\Controllers\SchoolInvitationController;
use App\Http\Controllers\SchoolOnboardingController;
use App\Http\Controllers\SchoolTeamController;
use App\Http\Controllers\SchoolSettingsController;
use App\Http\Controllers\DashboardController;

Route::get('/', fn () => view('schools.welcome'))->name('home');

Route::get('/invitations/{token}', [SchoolInvitationController::class, 'show'])
    ->name('school.invitations.show');
Route::post('/invitations/{token}', [SchoolInvitationController::class, 'accept'])
    ->name('school.invitations.accept');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/school/setup', [SchoolOnboardingController::class, 'create'])->name('school.setup.create');
    Route::post('/school/setup', [SchoolOnboardingController::class, 'store'])->name('school.setup.store');

    Route::middleware('super.admin')->prefix('owner')->name('owner.')->group(function () {
        Route::get('/schools', [OwnerSchoolController::class, 'index'])->name('dashboard');
        Route::post('/school-applications/{application}/approve', [OwnerSchoolController::class, 'approveApplication'])
            ->name('applications.approve');
        Route::post('/school-applications/{application}/reject', [OwnerSchoolController::class, 'rejectApplication'])
            ->name('applications.reject');
        Route::post('/schools/{school}/suspend', [OwnerSchoolController::class, 'suspendSchool'])
            ->name('schools.suspend');
        Route::post('/schools/{school}/reactivate', [OwnerSchoolController::class, 'reactivateSchool'])
            ->name('schools.reactivate');
        Route::put('/schools/{school}/subscription', [OwnerSchoolController::class, 'updateSubscription'])
            ->name('schools.subscription.update');
        Route::delete('/schools/{school}', [OwnerSchoolController::class, 'destroySchool'])
            ->name('schools.destroy');
    });

    Route::middleware(['school.tenant', 'school.access'])->group(function () {
    Route::get('/updates', fn () => view('updates.index'))->name('updates.index');
    Route::post('/finances/subsidies', [PaymentController::class, 'storeSubsidy'])->name('finances.subsidies.store');
    Route::middleware('school.settings.access')->prefix('/school/settings')->name('school.settings.')->group(function () {
        Route::get('/', [SchoolSettingsController::class, 'index'])->name('index');
        Route::put('/', [SchoolSettingsController::class, 'update'])->name('update');
        Route::put('/appearance', [SchoolSettingsController::class, 'updateAppearance'])->name('appearance');
        Route::put('/card', [SchoolSettingsController::class, 'updateCard'])->name('card');
        Route::post('/photos', [SchoolSettingsController::class, 'storePhoto'])->name('photos.store');
        Route::put('/photos/{photo}/primary', [SchoolSettingsController::class, 'setPrimaryPhoto'])->name('photos.primary');
        Route::delete('/photos/{photo}', [SchoolSettingsController::class, 'destroyPhoto'])->name('photos.destroy');
    });
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('school.admin')->group(function () {
        Route::middleware('school.team.access')->group(function () {
            Route::get('/school/team', [SchoolTeamController::class, 'index'])->name('school.team.index');
            Route::post('/school/team/invitations', [SchoolTeamController::class, 'store'])->name('school.team.invitations.store');
        });
    });

    Route::get('/students/{student}/birth-certificate', [StudentController::class, 'birthCertificate'])
        ->name('students.birth-certificate');
    Route::resource('students', StudentController::class);
    Route::get('teachers/attendance', [TeacherController::class, 'attendance'])->name('teachers.attendance');
    Route::post('teachers/attendance/scan', [TeacherController::class, 'scan'])->name('teachers.attendance.scan');
    Route::resource('teachers', TeacherController::class);
    // Routes pour les années scolaires
    Route::get('/school-years', [SchoolYearController::class, 'index'])->name('school-years.index');
    Route::post('/school-years', [SchoolYearController::class, 'store'])->name('school-years.store');
    Route::post('/school-years/close', [SchoolYearController::class, 'close'])->name('school-years.close');
    Route::put('/school-years/{schoolYear}/set-current', [SchoolYearController::class, 'setCurrent'])->name('school-years.set-current');
    Route::delete('/school-years/{schoolYear}', [SchoolYearController::class, 'destroy'])->name('school-years.destroy');

    Route::get('/classes/{classe}/print-students', [ClassRoomController::class, 'printStudents'])->name('classes.print-students');
    Route::get('/classes/{classe}/print-grade-sheet', [ClassRoomController::class, 'printGradeSheet'])->name('classes.print-grade-sheet');
    Route::resource('classes', ClassRoomController::class)->parameters(['classes' => 'classe']);
    Route::resource('subjects', SubjectController::class);
    Route::get('/teaching-assignments', [TeachingAssignmentController::class, 'index'])->name('teaching-assignments.index');
    Route::post('/teaching-assignments', [TeachingAssignmentController::class, 'store'])->name('teaching-assignments.store');
    Route::delete('/teaching-assignments/{teachingAssignment}', [TeachingAssignmentController::class, 'destroy'])->name('teaching-assignments.destroy');
    Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
    Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
    Route::delete('/timetable/{timetableEntry}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
    Route::get('/finances', [PaymentController::class, 'index'])->name('finances.index');
    Route::get('/finances/create', [PaymentController::class, 'create'])->name('finances.create');
    Route::post('/finances', [PaymentController::class, 'store'])->name('finances.store');
    Route::get('/finances/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('finances.receipt');
    Route::delete('/finances/{payment}', [PaymentController::class, 'destroy'])->name('finances.destroy');
    Route::get('/finances/expenses/create', [ExpenseController::class, 'create'])->name('finances.expenses.create');
    Route::post('/finances/expenses', [ExpenseController::class, 'store'])->name('finances.expenses.store');
    Route::delete('/finances/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('finances.expenses.destroy');
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/create', [NoteController::class, 'create'])->name('notes.create');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::get('/notes/bulletin', [NoteController::class, 'bulletin'])->name('notes.bulletin');
    Route::get('/notes/bulletin/class', [NoteController::class, 'classBulletin'])->name('notes.class-bulletin');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::get('/parents', [ParentController::class, 'index'])->name('parents.index');
    Route::get('/parents/{parentKey}/edit', [ParentController::class, 'edit'])->name('parents.edit');
    Route::put('/parents/{parentKey}', [ParentController::class, 'update'])->name('parents.update');
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{student}/{type}', [DocumentController::class, 'print'])
        ->whereIn('type', ['inscription', 'scolarite', 'transfert', 'scolaire'])
        ->name('documents.print');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
});
