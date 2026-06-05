<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use App\Models\ContactSubmission;
use App\Services\AcademyDataStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PanelController extends Controller
{
    public function dashboard(Request $request): RedirectResponse
    {
        return $request->user()->role === 'alumno'
            ? redirect()->route('panel.alumno')
            : redirect()->route('panel.profesor');
    }

    public function alumno(Request $request, AcademyDataStore $store): Response
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        $store->seedMaterialsIfNeeded();

        $visibleCourses = Course::query()
            ->with(['category:id,name', 'teacher:id,name,email,avatar'])
            ->where('is_hidden', false)
            ->orderBy('title')
            ->get();

        $enrolledIds = $store->ensureDefaultEnrollment((int) $request->user()->id, $visibleCourses);

        $enrolledCourses = $visibleCourses
            ->whereIn('id', $enrolledIds)
            ->sortBy(fn (Course $course) => array_search((int) $course->id, $enrolledIds, true))
            ->values();

        $progress = $store->getProgressForStudent((int) $request->user()->id);
        $learningState = $store->getLearningStateForStudent((int) $request->user()->id);

        $materialsByCourse = $store->getMaterialsForCourses($enrolledIds);
        $announcements = $store->getAnnouncementsForCourses($enrolledIds);

        $teacherContacts = User::query()
            ->whereIn('role', ['profesor', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'avatar', 'role']);

        $messages = $store->allMessagesForUser((int) $request->user()->id);

        $userIds = collect($messages)->pluck('from_id')
            ->merge(collect($messages)->pluck('to_id'))
            ->merge(collect($announcements)->pluck('author_id'))
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $messages = collect($messages)->map(function (array $message) use ($users) {
            $message['from_name'] = $users->get((int) ($message['from_id'] ?? 0))->name ?? 'Usuario';
            $message['to_name'] = $users->get((int) ($message['to_id'] ?? 0))->name ?? 'Usuario';

            return $message;
        })->all();

        $announcements = collect($announcements)->map(function (array $announcement) use ($users) {
            $announcement['author_name'] = $users->get((int) ($announcement['author_id'] ?? 0))->name ?? 'Profesorado';

            return $announcement;
        })->all();

        $threads = collect($messages)
            ->groupBy(fn (array $message) => (string) ($message['thread_id'] ?? 'sin-hilo'))
            ->map(fn ($threadMessages) => collect($threadMessages)->sortByDesc('created_at')->first())
            ->values()
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return Inertia::render('Panels/AlumnoDashboard', [
            'enrolledCourses' => $enrolledCourses,
            'progress' => $progress,
            'learningState' => $learningState,
            'materialsByCourse' => $materialsByCourse,
            'announcements' => $announcements,
            'messages' => $messages,
            'threads' => $threads,
            'teacherContacts' => $teacherContacts,
            'teacherDirectory' => $teacherContacts,
        ]);
    }

    public function alumnoCourse(Request $request, Course $course, AcademyDataStore $store): Response
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        if ($course->is_hidden) {
            abort(404);
        }

        $enrolledIds = $store->getStudentCourseIds((int) $request->user()->id);

        if (! in_array((int) $course->id, $enrolledIds, true)) {
            abort(403);
        }

        $progress = $store->getProgressForStudent((int) $request->user()->id);
        $learningState = $store->getLearningStateForStudent((int) $request->user()->id);

        return Inertia::render('Panels/AlumnoCourseView', [
            'course' => $course->load(['category:id,name', 'teacher:id,name,email,avatar']),
            'materials' => $store->getMaterialsForCourses([(int) $course->id])[(string) $course->id] ?? [],
            'tasks' => $store->getCourseTaskDefinitions((int) $course->id),
            'exam' => $store->getCourseExamDefinition((int) $course->id),
            'progress' => (int) ($progress[(string) $course->id] ?? 0),
            'state' => $learningState[(string) $course->id] ?? null,
        ]);
    }

    public function alumnoTaskSubmit(Request $request, Course $course, int $taskNumber, AcademyDataStore $store): Response
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        if (! in_array($taskNumber, [1, 2, 3], true)) {
            abort(404);
        }

        if ($course->is_hidden) {
            abort(404);
        }

        $enrolledIds = $store->getStudentCourseIds((int) $request->user()->id);

        if (! in_array((int) $course->id, $enrolledIds, true)) {
            abort(403);
        }

        $tasks = collect($store->getCourseTaskDefinitions((int) $course->id));
        $task = $tasks->first(fn (array $item) => (int) ($item['id'] ?? 0) === $taskNumber);

        if (! $task || ! (bool) ($task['is_visible'] ?? true)) {
            abort(404);
        }

        $learningState = $store->getLearningStateForStudent((int) $request->user()->id);
        $courseState = $learningState[(string) $course->id] ?? [];

        return Inertia::render('Panels/AlumnoTaskSubmitView', [
            'course' => $course->load(['category:id,name', 'teacher:id,name,email,avatar']),
            'task' => $task,
            'taskNumber' => $taskNumber,
            'alreadySubmitted' => (bool) ($courseState['tasks_submitted'][$taskNumber - 1] ?? false),
            'submission' => $courseState['task_submissions']['task'.$taskNumber] ?? null,
        ]);
    }

    public function profesor(Request $request, AcademyDataStore $store): Response
    {
        if (! in_array($request->user()->role, ['profesor', 'admin'], true)) {
            abort(403);
        }

        $store->seedMaterialsIfNeeded();

        $isAdmin = $request->user()->role === 'admin';
        $courses = $store->visibleAndHiddenCoursesForRole((int) $request->user()->id, $isAdmin);
        $courseIds = $courses->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

        $enrollmentMap = $store->getEnrollmentMap();

        $students = User::query()
            ->where('role', 'alumno')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'avatar']);

        $materialsByCourse = $store->getMaterialsForCourses($courseIds);
        $announcements = $store->getAnnouncementsForTeacher($courseIds);
        $messages = $store->allMessagesForUser((int) $request->user()->id);

        $userIds = collect($messages)->pluck('from_id')
            ->merge(collect($messages)->pluck('to_id'))
            ->merge(collect($announcements)->pluck('author_id'))
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $messages = collect($messages)->map(function (array $message) use ($users) {
            $message['from_name'] = $users->get((int) ($message['from_id'] ?? 0))->name ?? 'Usuario';
            $message['to_name'] = $users->get((int) ($message['to_id'] ?? 0))->name ?? 'Usuario';

            return $message;
        })->all();

        $announcements = collect($announcements)->map(function (array $announcement) use ($users) {
            $announcement['author_name'] = $users->get((int) ($announcement['author_id'] ?? 0))->name ?? 'Profesorado';

            return $announcement;
        })->all();

        $studentProgress = $students->mapWithKeys(function (User $student) use ($store, $courseIds) {
            $progressMap = $store->getProgressForStudent((int) $student->id);
            $filtered = collect($progressMap)
                ->filter(fn ($value, $courseId) => in_array((int) $courseId, $courseIds, true))
                ->map(fn ($value) => (int) $value)
                ->all();

            return [(string) $student->id => $filtered];
        });

        $activeStudents = $students->map(function (User $student) use ($courses, $enrollmentMap) {
            $studentCourseIds = collect($enrollmentMap[(string) $student->id] ?? [])->map(fn ($id) => (int) $id);
            $sharedCourses = $courses
                ->whereIn('id', $studentCourseIds)
                ->values()
                ->map(fn (Course $course) => ['id' => $course->id, 'title' => $course->title])
                ->values();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'avatar' => $student->avatar,
                'shared_courses' => $sharedCourses,
            ];
        })->filter(fn (array $student) => count($student['shared_courses']) > 0)->values();

        $threads = collect($messages)
            ->groupBy(fn (array $message) => (string) ($message['thread_id'] ?? 'sin-hilo'))
            ->map(fn ($threadMessages) => collect($threadMessages)->sortByDesc('created_at')->first())
            ->values()
            ->sortByDesc('created_at')
            ->values()
            ->all();

        return Inertia::render('Panels/ProfesorDashboard', [
            'courses' => $courses,
            'students' => $students,
            'activeStudents' => $activeStudents,
            'announcements' => $announcements,
            'isAdmin' => $isAdmin,
            'panelTitle' => $isAdmin ? 'Panel Profesor Administrador' : 'Panel Profesor',
            'studentProgress' => $studentProgress,
        ]);
    }

    public function messages(Request $request, AcademyDataStore $store): Response
    {
        $user = $request->user();
        $isStudent = $user->role === 'alumno';
        $isAdmin = $user->role === 'admin';

        if (! in_array($user->role, ['alumno', 'profesor', 'admin'], true)) {
            abort(403);
        }

        $messages = $store->allMessagesForUser((int) $user->id);

        $userIds = collect($messages)->pluck('from_id')
            ->merge(collect($messages)->pluck('to_id'))
            ->unique()
            ->values();

        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'email', 'role', 'avatar'])
            ->keyBy('id');

        $messages = collect($messages)->map(function (array $message) use ($users) {
            $message['from_name'] = $users->get((int) ($message['from_id'] ?? 0))->name ?? 'Usuario';
            $message['to_name'] = $users->get((int) ($message['to_id'] ?? 0))->name ?? 'Usuario';

            return $message;
        })->all();

        $threads = collect($messages)
            ->groupBy(fn (array $message) => (string) ($message['thread_id'] ?? 'sin-hilo'))
            ->map(fn ($threadMessages) => collect($threadMessages)->sortByDesc('created_at')->first())
            ->values()
            ->sortByDesc('created_at')
            ->values()
            ->all();

        $contacts = $isStudent
            ? User::query()
                ->whereIn('role', ['profesor', 'admin'])
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'avatar', 'role'])
            : User::query()
                ->when(! $isAdmin, fn ($q) => $q->where('role', 'alumno')
                    ->orWhere(fn ($q2) => $q2->whereIn('role', ['profesor', 'admin'])))
                ->when($isAdmin, fn ($q) => $q->whereIn('role', ['alumno', 'profesor', 'admin']))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'avatar', 'role']);

        $courses = $isStudent
            ? Course::query()
                ->whereIn('id', $store->getStudentCourseIds((int) $user->id))
                ->where('is_hidden', false)
                ->orderBy('title')
                ->get(['id', 'title'])
            : $store->visibleAndHiddenCoursesForRole((int) $user->id, $isAdmin)
                ->map(fn (Course $course) => ['id' => $course->id, 'title' => $course->title])
                ->values();

        return Inertia::render('Panels/MessagesCenter', [
            'panelTitle' => 'Mensajes',
            'contacts' => $contacts,
            'messages' => $messages,
            'threads' => $threads,
            'courses' => $courses,
            'isStudent' => $isStudent,
        ]);
    }

    public function announcementsCenter(Request $request, AcademyDataStore $store): Response
    {
        $user = $request->user();

        if (! in_array($user->role, ['alumno', 'profesor', 'admin'], true)) {
            abort(403);
        }

        $isStudent = $user->role === 'alumno';
        $isAdmin = $user->role === 'admin';

        $courses = $isStudent
            ? Course::query()
                ->whereIn('id', $store->getStudentCourseIds((int) $user->id))
                ->where('is_hidden', false)
                ->orderBy('title')
                ->get(['id', 'title'])
            : $store->visibleAndHiddenCoursesForRole((int) $user->id, $isAdmin)
                ->map(fn (Course $course) => ['id' => $course->id, 'title' => $course->title])
                ->values();

        $courseIds = collect($courses)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $announcements = $isStudent
            ? $store->getAnnouncementsForCourses($courseIds)
            : $store->getAnnouncementsForTeacher($courseIds);

        $userIds = collect($announcements)->pluck('author_id')->unique()->values();
        $users = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $announcements = collect($announcements)->map(function (array $announcement) use ($users) {
            $announcement['author_name'] = $users->get((int) ($announcement['author_id'] ?? 0))->name ?? 'Profesorado';
            $announcement['type'] = (string) ($announcement['type'] ?? 'general');

            return $announcement;
        })->all();

        return Inertia::render('Panels/AnnouncementsCenter', [
            'panelTitle' => 'Tablón de Anuncios',
            'announcements' => $announcements,
            'courses' => $courses,
            'isStudent' => $isStudent,
            'centerInfo' => [
                'phone' => '+34 96 123 45 67',
                'email' => 'info@eleviaacademy.com',
                'address' => 'Valencia · Comunidad Valenciana',
            ],
            'calendarPdfUrl' => '/imagenes/fondos/calendario_escolar_cv_2025_2026.pdf',
        ]);
    }

    public function adminStudents(Request $request): Response
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $students = User::query()
            ->where('role', 'alumno')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar']);

        return Inertia::render('Panels/AdminStudentsManagement', [
            'panelTitle' => 'Gestión de Alumnos',
            'students' => $students,
        ]);
    }

    public function adminTeachers(Request $request): Response
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $teachers = User::query()
            ->where('role', 'profesor')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'avatar']);

        return Inertia::render('Panels/AdminTeachersManagement', [
            'panelTitle' => 'Gestión de Profesores',
            'teachers' => $teachers,
        ]);
    }

    public function profesorCourse(Request $request, Course $course, AcademyDataStore $store): Response
    {
        if (! in_array($request->user()->role, ['profesor', 'admin'], true)) {
            abort(403);
        }

        $isAdmin = $request->user()->role === 'admin';

        if (! $isAdmin && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $enrollmentMap = $store->getEnrollmentMap();

        $students = User::query()
            ->where('role', 'alumno')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'avatar'])
            ->map(function (User $student) use ($enrollmentMap, $course, $store) {
                $isEnrolled = in_array((int) $course->id, collect($enrollmentMap[(string) $student->id] ?? [])->map(fn ($id) => (int) $id)->all(), true);
                $state = $store->getLearningStateForStudent((int) $student->id)[(string) $course->id] ?? null;
                $progress = (int) ($store->getProgressForStudent((int) $student->id)[(string) $course->id] ?? 0);

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'avatar' => $student->avatar,
                    'is_enrolled' => $isEnrolled,
                    'progress' => $progress,
                    'state' => $state,
                ];
            })
            ->values();

        return Inertia::render('Panels/ProfesorCourseView', [
            'course' => $course->load(['category:id,name', 'teacher:id,name,email,avatar']),
            'students' => $students,
            'materials' => $store->getMaterialsForCourses([(int) $course->id])[(string) $course->id] ?? [],
            'tasks' => $store->getCourseTaskDefinitions((int) $course->id),
            'exam' => $store->getCourseExamDefinition((int) $course->id),
            'isAdmin' => $isAdmin,
            'panelTitle' => $isAdmin ? 'Panel Profesor Administrador' : 'Panel Profesor',
        ]);
    }

    public function adminContactSubmissions(Request $request): Response
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }

        $submissions = ContactSubmission::orderBy('created_at', 'desc')->get();

        return Inertia::render('Panels/AdminContactSubmissions', [
            'submissions' => $submissions,
        ]);
    }

    public function newCourse(Request $request): Response
    {
        $isAdmin = $request->user()->role === 'admin';

        if ($request->user()->role !== 'profesor' && ! $isAdmin) {
            abort(403);
        }

        $categories = Category::all();

        return Inertia::render('Panels/ProfesorCourseForm', [
            'course' => null,
            'categories' => $categories,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function editCourse(Request $request, Course $course): Response
    {
        $isAdmin = $request->user()->role === 'admin';

        if ($request->user()->role !== 'profesor' && ! $isAdmin) {
            abort(403);
        }

        if ($course->teacher_id !== $request->user()->id && ! $isAdmin) {
            abort(403);
        }

        $categories = Category::all();

        return Inertia::render('Panels/ProfesorCourseForm', [
            'course' => $course,
            'categories' => $categories,
            'isAdmin' => $isAdmin,
        ]);
    }
}
