<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PanelActionsController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
use App\Models\Course;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

Route::get('/', function () {
    $courses = Schema::hasTable('courses')
        ? Course::query()
            ->with(['category:id,name', 'teacher:id,name'])
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
        : collect();

    return Inertia::render('Welcome', [
        'courses' => $courses,
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/dashboard', [PanelController::class, 'dashboard'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');

Route::get('/nosotros', function () {
    return Inertia::render('Nosotros');
})->name('nosotros');

Route::get('/contacto', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contacto', [ContactController::class, 'submit'])->name('contact.submit');

Route::get('/legal/privacidad', function () {
    return Inertia::render('Legal/Privacy');
})->name('legal.privacy');

Route::get('/legal/terminos', function () {
    return Inertia::render('Legal/Terms');
})->name('legal.terms');

Route::get('/legal/cookies', function () {
    return Inertia::render('Legal/Cookies');
})->name('legal.cookies');

Route::middleware('auth')->group(function () {
    Route::get('/panel/alumno', [PanelController::class, 'alumno'])->name('panel.alumno');
    Route::get('/panel/alumno/curso/{course}', [PanelController::class, 'alumnoCourse'])->name('panel.alumno.course');
    Route::get('/panel/alumno/curso/{course}/tareas/{taskNumber}/entregar', [PanelController::class, 'alumnoTaskSubmit'])->name('panel.alumno.task.submit-view');
    Route::get('/panel/profesor', [PanelController::class, 'profesor'])->name('panel.profesor');
    Route::get('/panel/profesor/curso/nuevo', [PanelController::class, 'newCourse'])->name('panel.profesor.course.new');
    Route::post('/panel/profesor/curso', [PanelActionsController::class, 'createCourse'])->name('panel.profesor.course.store');
    Route::get('/panel/profesor/curso/{course}/editar', [PanelController::class, 'editCourse'])->name('panel.profesor.course.edit');
    Route::patch('/panel/profesor/curso/{course}', [PanelActionsController::class, 'updateCourse'])->name('panel.profesor.course.update');
    Route::get('/panel/profesor/curso/{course}', [PanelController::class, 'profesorCourse'])->name('panel.profesor.course');
    Route::get('/panel/mensajes', [PanelController::class, 'messages'])->name('panel.messages');
    Route::get('/panel/anuncios', [PanelController::class, 'announcementsCenter'])->name('panel.announcements');
    Route::get('/panel/admin/alumnos', [PanelController::class, 'adminStudents'])->name('panel.admin.students');
    Route::get('/panel/admin/profesores', [PanelController::class, 'adminTeachers'])->name('panel.admin.teachers');

    Route::post('/panel/mensajes', [PanelActionsController::class, 'sendMessage'])->name('panel.messages.send');
    Route::delete('/panel/mensajes/hilo', [PanelActionsController::class, 'deleteThread'])->name('panel.messages.thread.delete');

    Route::post('/panel/alumno/contenido-completado', [PanelActionsController::class, 'completeContent'])->name('panel.alumno.complete-content');
    Route::post('/panel/alumno/tareas/entregar', [PanelActionsController::class, 'submitTask'])->name('panel.alumno.submit-task');
    Route::post('/panel/alumno/tareas/entregar-con-adjunto', [PanelActionsController::class, 'submitTaskWithAttachment'])->name('panel.alumno.submit-task-detailed');

    Route::post('/panel/profesor/matriculas', [PanelActionsController::class, 'updateEnrollment'])->name('panel.enrollment.update');
    Route::post('/panel/profesor/progreso', [PanelActionsController::class, 'updateProgress'])->name('panel.progress.update');
    Route::post('/panel/profesor/tareas/definicion', [PanelActionsController::class, 'updateCourseTasks'])->name('panel.tasks.update');
    Route::post('/panel/profesor/tareas/calificar', [PanelActionsController::class, 'gradeTask'])->name('panel.tasks.grade');
    Route::post('/panel/profesor/evaluacion/definicion', [PanelActionsController::class, 'updateExamDefinition'])->name('panel.exam.update');
    Route::post('/panel/profesor/evaluacion/resultado', [PanelActionsController::class, 'setExamResult'])->name('panel.exam.result');
    Route::post('/panel/profesor/curso/visibilidad', [PanelActionsController::class, 'toggleCourseVisibility'])->name('panel.course.visibility');
    Route::post('/panel/profesor/materiales/url', [PanelActionsController::class, 'addMaterialUrl'])->name('panel.materials.add-url');
    Route::post('/panel/profesor/materiales/pdf', [PanelActionsController::class, 'addMaterialPdf'])->name('panel.materials.add-pdf');
    Route::delete('/panel/profesor/materiales/{courseId}/{materialId}', [PanelActionsController::class, 'deleteMaterial'])->name('panel.materials.delete');
    Route::post('/panel/profesor/anuncios', [PanelActionsController::class, 'addAnnouncement'])->name('panel.announcements.add');
    Route::delete('/panel/profesor/anuncios/{announcementId}', [PanelActionsController::class, 'deleteAnnouncement'])->name('panel.announcements.delete');

    Route::post('/panel/admin/usuarios', [PanelActionsController::class, 'adminCreateUser'])->name('panel.admin.users.create');
    Route::patch('/panel/admin/usuarios', [PanelActionsController::class, 'adminUpdateUser'])->name('panel.admin.users.update');
    Route::delete('/panel/admin/usuarios', [PanelActionsController::class, 'adminDeleteUser'])->name('panel.admin.users.delete');
    Route::delete('/panel/admin/cursos', [PanelActionsController::class, 'adminDeleteCourse'])->name('panel.admin.courses.delete');
    Route::get('/panel/admin/contactos', [PanelController::class, 'adminContactSubmissions'])->name('panel.admin.contacts');
});

Route::fallback(function () {
    return Inertia::render('Errors/NotFound')
        ->toResponse(request())
        ->setStatusCode(404);
});
