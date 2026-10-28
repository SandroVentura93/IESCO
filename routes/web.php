<?php

use App\Http\Controllers\DriveProxyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/paginas/{slug}', [PageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('pages.show');

// Simple auth routes (login/logout) — lightweight replacement when no auth scaffolding
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboards and admin file manager (protected by auth)
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectorController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\FileManagerController;
use App\Http\Controllers\PedagogyController;
use App\Http\Controllers\StudentController;

Route::middleware('auth')->group(function () {
    Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.dashboard')->middleware('role:admin');
    Route::get('/admin/files-panel', [DashboardController::class, 'filesPanel'])->name('admin.files.panel')->middleware('role:admin');
    Route::get('/admin/media-library', [FileManagerController::class, 'mediaLibrary'])->name('admin.media-library.index')->middleware('role:admin');
    Route::post('/admin/media-library', [FileManagerController::class, 'storeMediaLibraryFile'])->name('admin.media-library.store')->middleware('role:admin');
    Route::get('/admin/media-library/{id}/preview', [FileManagerController::class, 'previewMediaLibraryFile'])->whereNumber('id')->name('admin.media-library.preview')->middleware('role:admin');
    Route::delete('/admin/media-library/{id}', [FileManagerController::class, 'destroyMediaLibraryFile'])->whereNumber('id')->name('admin.media-library.destroy')->middleware('role:admin');
    Route::get('/admin/users-maintenance', [DashboardController::class, 'usersMaintenance'])->name('admin.users.maintenance')->middleware('role:admin');
    Route::get('/director', [DirectorController::class, 'dashboard'])->name('director.dashboard')->middleware('role:admin|director');
    Route::get('/director/resources', [DirectorController::class, 'resources'])->name('director.resources.index')->middleware('role:admin|director');
    Route::post('/director/resources', [DirectorController::class, 'storeResource'])->name('director.resources.store')->middleware('role:admin|director');
    Route::get('/director/resources/files/{id}/download', [DirectorController::class, 'downloadStoredFile'])->whereNumber('id')->name('director.resources.files.download')->middleware('role:admin|director');
    Route::delete('/director/resources/files/{id}', [DirectorController::class, 'destroyStoredFile'])->whereNumber('id')->name('director.resources.files.destroy')->middleware('role:admin|director');
    Route::delete('/director/resources/{id}', [DirectorController::class, 'destroyResource'])->whereNumber('id')->name('director.resources.destroy')->middleware('role:admin|director');
    Route::get('/teacher', [PedagogyController::class, 'index'])->name('teacher.dashboard')->middleware('role:docente');
    Route::get('/teacher/courses', [PedagogyController::class, 'courses'])->name('teacher.courses')->middleware('role:docente');
    Route::get('/teacher/resources', [PedagogyController::class, 'resources'])->name('teacher.resources.index')->middleware('role:docente');
    Route::get('/teacher/resources/create', [PedagogyController::class, 'createResource'])->name('teacher.resources.create')->middleware('role:docente');
    // assigning courses is admin-only
    Route::post('/teacher/assign-course', [PedagogyController::class, 'assignCourse'])->name('teacher.assign.course')->middleware('role:admin');
    Route::post('/teacher/resources/upload', [PedagogyController::class, 'uploadResource'])->name('teacher.resources.upload')->middleware('role:docente');
    Route::delete('/teacher/resources/files/{id}', [PedagogyController::class, 'destroyStoredFile'])->whereNumber('id')->name('teacher.resources.files.destroy')->middleware('role:docente');
    Route::delete('/teacher/resources/{id}', [PedagogyController::class, 'destroyResource'])->whereNumber('id')->name('teacher.resources.destroy')->middleware('role:docente');
    Route::get('/student', [DashboardController::class, 'student'])->name('student.dashboard');

    // Admin file manager actions (controller checks role)
    Route::post('/admin/files', [FileManagerController::class, 'store'])->name('admin.files.store')->middleware('role:admin');
    Route::get('/admin/files/{id}/edit', [FileManagerController::class, 'edit'])->whereNumber('id')->name('admin.files.edit')->middleware('role:admin');
    Route::put('/admin/files/{id}', [FileManagerController::class, 'update'])->whereNumber('id')->name('admin.files.update')->middleware('role:admin');
    Route::post('/admin/files/{id}/publish', [FileManagerController::class, 'publish'])->name('admin.files.publish')->middleware('role:admin');
    Route::post('/admin/files/{id}/unpublish', [FileManagerController::class, 'unpublish'])->name('admin.files.unpublish')->middleware('role:admin');
    Route::post('/admin/files/{id}/section', [FileManagerController::class, 'updateSection'])->name('admin.files.update_section')->middleware('role:admin');
    Route::delete('/admin/files/{id}', [FileManagerController::class, 'destroy'])->name('admin.files.destroy')->middleware('role:admin');
    Route::get('/admin/files/{id}/download', [FileManagerController::class, 'download'])->name('admin.files.download')->middleware('role:admin');
    Route::get('/admin/files/{id}/preview', [FileManagerController::class, 'preview'])->name('admin.files.preview')->middleware('role:admin');
    // User management
    Route::get('/admin/users', [UsersController::class, 'index'])->name('admin.users.index')->middleware('role:admin');
    Route::get('/admin/users/create', [UsersController::class, 'create'])->name('admin.users.create')->middleware('role:admin');
    Route::post('/admin/users', [UsersController::class, 'store'])->name('admin.users.store')->middleware('role:admin');
    Route::get('/admin/users/{id}/edit', [UsersController::class, 'edit'])->name('admin.users.edit')->middleware('role:admin');
    Route::put('/admin/users/{id}', [UsersController::class, 'update'])->name('admin.users.update')->middleware('role:admin');
    Route::delete('/admin/users/{id}', [UsersController::class, 'destroy'])->name('admin.users.destroy')->middleware('role:admin');
    // Courses CRUD
    Route::get('/admin/courses', [CoursesController::class, 'index'])->name('admin.courses.index')->middleware('role:admin|director');
    Route::get('/admin/courses/create', [CoursesController::class, 'create'])->name('admin.courses.create')->middleware('role:admin|director');
    Route::post('/admin/courses', [CoursesController::class, 'store'])->name('admin.courses.store')->middleware('role:admin|director');
    Route::get('/admin/courses/{id}/edit', [CoursesController::class, 'edit'])->name('admin.courses.edit')->middleware('role:admin|director');
    Route::put('/admin/courses/{id}', [CoursesController::class, 'update'])->name('admin.courses.update')->middleware('role:admin|director');
    Route::delete('/admin/courses/{id}', [CoursesController::class, 'destroy'])->name('admin.courses.destroy')->middleware('role:admin|director');
    // Students CRUD
    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students.index')->middleware('role:admin|director');
    Route::get('/admin/students/create', [StudentController::class, 'create'])->name('admin.students.create')->middleware('role:admin|director');
    Route::post('/admin/students', [StudentController::class, 'store'])->name('admin.students.store')->middleware('role:admin|director');
    Route::get('/admin/students/{id}/edit', [StudentController::class, 'edit'])->name('admin.students.edit')->middleware('role:admin|director');
    Route::put('/admin/students/{id}', [StudentController::class, 'update'])->name('admin.students.update')->middleware('role:admin|director');
    Route::delete('/admin/students/{id}', [StudentController::class, 'destroy'])->name('admin.students.destroy')->middleware('role:admin|director');
    // Student enrollment
    Route::get('/admin/enrollments', [EnrollmentController::class, 'index'])->name('admin.enrollments.index')->middleware('role:admin|director');
    Route::get('/admin/enrollments/create', [EnrollmentController::class, 'create'])->name('admin.enrollments.create')->middleware('role:admin|director');
    Route::post('/admin/enrollments/students', [EnrollmentController::class, 'storeStudent'])->name('admin.enrollments.students.store')->middleware('role:admin|director');
    Route::post('/admin/enrollments', [EnrollmentController::class, 'store'])->name('admin.enrollments.store')->middleware('role:admin|director');
    Route::get('/admin/enrollments/{id}/edit', [EnrollmentController::class, 'edit'])->whereNumber('id')->name('admin.enrollments.edit')->middleware('role:admin|director');
    Route::put('/admin/enrollments/{id}', [EnrollmentController::class, 'update'])->whereNumber('id')->name('admin.enrollments.update')->middleware('role:admin|director');
    Route::get('/admin/enrollments/{id}/ficha', [EnrollmentController::class, 'ficha'])->whereNumber('id')->name('admin.enrollments.ficha')->middleware('role:admin|director');
    Route::patch('/admin/enrollments/{id}/status', [EnrollmentController::class, 'updateStatus'])->whereNumber('id')->name('admin.enrollments.status')->middleware('role:admin|director');
    // Assignments: assign course to teacher
    Route::get('/admin/assignments/create', [AssignmentController::class, 'create'])->name('admin.assignments.create')->middleware('role:admin|director');
    Route::post('/admin/assignments', [AssignmentController::class, 'store'])->name('admin.assignments.store')->middleware('role:admin|director');
    Route::get('/admin/assignments/{id}/edit', [AssignmentController::class, 'edit'])->whereNumber('id')->name('admin.assignments.edit')->middleware('role:admin|director');
    Route::put('/admin/assignments/{id}', [AssignmentController::class, 'update'])->whereNumber('id')->name('admin.assignments.update')->middleware('role:admin|director');
    Route::delete('/admin/assignments/{id}', [AssignmentController::class, 'destroy'])->whereNumber('id')->name('admin.assignments.destroy')->middleware('role:admin|director');
    // Pages management (inicio, quienes somos, etc.)
    Route::get('/admin/pages/sections/{section}/edit', [PageController::class, 'editSection'])
        ->name('admin.pages.sections.edit')
        ->middleware('role:admin');
    Route::put('/admin/pages/sections/{section}', [PageController::class, 'updateSection'])
        ->name('admin.pages.sections.update')
        ->middleware('role:admin');
    Route::resource('/admin/pages', PageController::class)
        ->except('show')
        ->names('admin.pages')
        ->middleware('role:admin');
    // Site settings
    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('admin.settings.index')->middleware('role:admin');
    Route::post('/admin/settings', [SettingsController::class, 'store'])->name('admin.settings.store')->middleware('role:admin');
    // Menus management
    Route::resource('/admin/menus', MenuController::class)->names('admin.menus')->middleware('role:admin');
    // Real-time-ish activity endpoint (polled by dashboard)
    Route::get('/admin/activity/recent-json', [ActivityController::class, 'recentJson'])->name('admin.activity.recent_json')->middleware('role:admin');
});

/*
|--------------------------------------------------------------------------
| Drive Proxy — evita exponer la API Key al cliente
| El frontend llama a estos endpoints en vez de Google Drive directamente.
|--------------------------------------------------------------------------
*/
Route::prefix('drive')->name('drive.')->group(function () {
    Route::get('/proyecto', [DriveProxyController::class, 'proyecto'])->name('proyecto');
    Route::get('/subfolder', [DriveProxyController::class, 'subfolder'])->name('subfolder');
    Route::get('/local-project', [DriveProxyController::class, 'localProject'])->name('local_project');
    Route::get('/novedades', [DriveProxyController::class, 'novedades'])->name('novedades');
    Route::get('/publicaciones', [DriveProxyController::class, 'publicaciones'])->name('publicaciones');
});

Route::get('/public/files/{id}', [FileManagerController::class, 'publicFile'])->whereNumber('id')->name('public.files.show');
