<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::query()
            ->with(['category:id,name', 'teacher:id,name'])
            ->where('is_hidden', false)
            ->orderBy('title')
            ->get();

        $categories = $courses
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->values();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'categories' => $categories,
        ]);
    }
}