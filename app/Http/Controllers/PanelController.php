<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Services\AcademyDataStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanelController extends Controller
{
    public function dashboard(Request $request): RedirectResponse
    {
        $role = $request->user()->role;

        if ($role === 'alumno') {
            return redirect()->route('panel.alumno');
        }

        return redirect()->route('panel.profesor');
    }

    public function alumno(Request $request, AcademyDataStore $store): Response
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        $store->seedMaterialsIfNeeded();

        $visibleCourses = Course::query()
            ->with(['category:id,name', 'teacher:id,name'])
            ->where('is_hidden', false)
            ->orderBy('title')
            ->get();

        $enrolledIds = $store->ensureDefaultEnrollment((int) $request->user()->id, $visibleCourses);
        $enrolledCourses = $visibleCourses
            ->whereIn('id', $enrolledIds)
            ->sortBy(fn (Course $course) => array_search((int) $course->id, $enrolledIds, true))
            ->values();

        $progress = $store->getProgressForStudent((int) $request->user()->id);
        $materialsByCourse = $store->getMaterialsForCourses($enrolledIds);
        $announcements = $store->getAnnouncementsForCourses($enrolledIds);

        $teacherContacts = $enrolledCourses
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values();

        $messages = $store->inboxForUser((int) $request->user()->id);

        $userIds = collect($messages)->pluck('from_id')
            ->merge(collect($announcements)->pluck('author_id'))
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $messages = collect($messages)->map(function (array $message) use ($users) {
            $message['from_name'] = $users->get((int) ($message['from_id'] ?? 0))->name ?? 'Usuario';
            return $message;
        })->all();

        $announcements = collect($announcements)->map(function (array $announcement) use ($users) {
            $announcement['author_name'] = $users->get((int) ($announcement['author_id'] ?? 0))->name ?? 'Profesorado';
            return $announcement;
        })->all();

        return Inertia::render('Panels/AlumnoDashboard', [
            'enrolledCourses' => $enrolledCourses,
            'progress' => $progress,
            'materialsByCourse' => $materialsByCourse,
            'announcements' => $announcements,
            'messages' => $messages,
            'teacherContacts' => $teacherContacts,
        ]);
    }

    public function profesor(Request $request, AcademyDataStore $store): Response
    {
        if (! in_array($request->user()->role, ['profesor', 'admin'], true)) {
            abort(403);
        }

        $store->seedMaterialsIfNeeded();

        $isAdmin = $request->user()->role === 'admin';
        $courses = $store->visibleCoursesForRole((int) $request->user()->id, $isAdmin);
        $courseIds = $courses->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $students = User::query()
            ->where('role', 'alumno')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $teacherContacts = User::query()
            ->whereIn('role', ['profesor', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $materialsByCourse = $store->getMaterialsForCourses($courseIds);
        $announcements = $store->getAnnouncementsForTeacher($courseIds);
        $messages = $store->inboxForUser((int) $request->user()->id);

        $userIds = collect($messages)->pluck('from_id')
            ->merge(collect($announcements)->pluck('author_id'))
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $messages = collect($messages)->map(function (array $message) use ($users) {
            $message['from_name'] = $users->get((int) ($message['from_id'] ?? 0))->name ?? 'Usuario';
            return $message;
        })->all();

        $announcements = collect($announcements)->map(function (array $announcement) use ($users) {
            $announcement['author_name'] = $users->get((int) ($announcement['author_id'] ?? 0))->name ?? 'Profesorado';
            return $announcement;
        })->all();

        return Inertia::render('Panels/ProfesorDashboard', [
            'courses' => $courses,
            'students' => $students,
            'enrollmentMap' => $store->getEnrollmentMap(),
            'materialsByCourse' => $materialsByCourse,
            'announcements' => $announcements,
            'messages' => $messages,
            'teacherContacts' => $teacherContacts,
            'isAdmin' => $isAdmin,
        ]);
    }
}
