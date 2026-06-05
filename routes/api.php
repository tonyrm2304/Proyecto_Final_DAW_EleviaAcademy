<?php

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/courses', function () {
    return Course::query()
        ->with(['category:id,name', 'teacher:id,name,email'])
        ->where('is_hidden', false)
        ->orderBy('title')
        ->get()
        ->map(fn (Course $course) => [
            'id' => $course->id,
            'title' => $course->title,
            'short_description' => $course->short_description,
            'long_description' => $course->long_description,
            'duration_hours' => $course->duration_hours,
            'category' => ['id' => $course->category->id, 'name' => $course->category->name],
            'teacher' => ['id' => $course->teacher->id, 'name' => $course->teacher->name],
            'is_hidden' => $course->is_hidden,
        ]);
});

Route::get('/courses/{id}', function (Course $course) {
    if ($course->is_hidden && !Auth::check()) {
        return response()->json(['error' => 'Curso no disponible'], 404);
    }

    return [
        'id' => $course->id,
        'title' => $course->title,
        'short_description' => $course->short_description,
        'long_description' => $course->long_description,
        'duration_hours' => $course->duration_hours,
        'category' => ['id' => $course->category->id, 'name' => $course->category->name],
        'teacher' => [
            'id' => $course->teacher->id,
            'name' => $course->teacher->name,
            'email' => $course->teacher->email,
        ],
        'is_hidden' => $course->is_hidden,
        'created_at' => $course->created_at->toIso8601String(),
    ];
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function () {
        return Auth::user();
    });

    Route::get('/my-courses', function () {
        $user = Auth::user();

        if ($user->role !== 'alumno') {
            return response()->json(['error' => 'Solo estudiantes pueden ver sus cursos'], 403);
        }

        $store = app('App\Services\AcademyDataStore');
        $enrolledIds = $store->getStudentCourseIds((int) $user->id);

        return Course::query()
            ->whereIn('id', $enrolledIds)
            ->get()
            ->map(fn (Course $course) => [
                'id' => $course->id,
                'title' => $course->title,
                'progress' => $store->getProgressForStudent((int) $user->id)[(string) $course->id] ?? 0,
            ]);
    });

    Route::get('/my-progress', function () {
        $user = Auth::user();

        if ($user->role !== 'alumno') {
            return response()->json(['error' => 'Solo estudiantes tienen progreso'], 403);
        }

        $store = app('App\Services\AcademyDataStore');
        $enrolledIds = $store->getStudentCourseIds((int) $user->id);
        $progress = $store->getProgressForStudent((int) $user->id);

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'courses' => collect($enrolledIds)->map(fn ($courseId) => [
                'course_id' => $courseId,
                'progress_percentage' => $progress[(string) $courseId] ?? 0,
            ])->values(),
        ];
    });
});


