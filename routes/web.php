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
            ->take(9)
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
    Route::get('/panel/profesor', [PanelController::class, 'profesor'])->name('panel.profesor');

    Route::post('/panel/mensajes', [PanelActionsController::class, 'sendMessage'])->name('panel.messages.send');

    Route::post('/panel/profesor/matriculas', [PanelActionsController::class, 'updateEnrollment'])->name('panel.enrollment.update');
    Route::post('/panel/profesor/progreso', [PanelActionsController::class, 'updateProgress'])->name('panel.progress.update');
    Route::post('/panel/profesor/materiales/url', [PanelActionsController::class, 'addMaterialUrl'])->name('panel.materials.add-url');
    Route::post('/panel/profesor/materiales/pdf', [PanelActionsController::class, 'addMaterialPdf'])->name('panel.materials.add-pdf');
    Route::delete('/panel/profesor/materiales/{courseId}/{materialId}', [PanelActionsController::class, 'deleteMaterial'])->name('panel.materials.delete');
    Route::post('/panel/profesor/anuncios', [PanelActionsController::class, 'addAnnouncement'])->name('panel.announcements.add');
    Route::delete('/panel/profesor/anuncios/{announcementId}', [PanelActionsController::class, 'deleteAnnouncement'])->name('panel.announcements.delete');
});

Route::fallback(function () {
    return Inertia::render('Errors/NotFound')
        ->toResponse(request())
        ->setStatusCode(404);
});
