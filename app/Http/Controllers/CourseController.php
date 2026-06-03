<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        // Cogemos solo los cursos que están publicados
        $courses = Course::where('status', 'published')->get();

        // Le mandamos los cursos a "Courses/Index"
        return Inertia::render('Courses/Index', [
            'courses' => $courses
        ]);
    }
}