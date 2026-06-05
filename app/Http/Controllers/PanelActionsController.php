<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Services\AcademyDataStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PanelActionsController extends Controller
{
    private function ensureTeacherOrAdmin(Request $request): void
    {
        if (! in_array($request->user()->role, ['profesor', 'admin'], true)) {
            abort(403);
        }
    }

    private function ensureAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403);
        }
    }

    public function updateEnrollment(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'action' => ['required', 'in:add,remove'],
        ]);

        $student = User::query()->where('role', 'alumno')->findOrFail($data['student_id']);
        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($data['action'] === 'add') {
            $store->addEnrollment((int) $student->id, (int) $course->id);
            return back()->with('success', 'Matrícula añadida correctamente.');
        }

        $store->removeEnrollment((int) $student->id, (int) $course->id);

        return back()->with('success', 'Matrícula eliminada correctamente.');
    }

    public function updateProgress(Request $request, AcademyDataStore $store): RedirectResponse
    {
        return back()->with('success', 'El progreso se calcula automáticamente por el sistema (50/30/20).');
    }

    public function completeContent(Request $request, AcademyDataStore $store): RedirectResponse
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($course->is_hidden) {
            abort(404);
        }

        $store->completeCourseContent((int) $request->user()->id, (int) $course->id);

        return back()->with('success', 'Contenido marcado como visualizado. (+50%)');
    }

    public function submitTask(Request $request, AcademyDataStore $store): RedirectResponse
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'task_number' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($course->is_hidden) {
            abort(404);
        }

        $store->submitTask((int) $request->user()->id, (int) $course->id, (int) $data['task_number']);

        return back()->with('success', 'Tarea registrada correctamente. (+10%)');
    }

    public function submitTaskWithAttachment(Request $request, AcademyDataStore $store): RedirectResponse
    {
        if ($request->user()->role !== 'alumno') {
            abort(403);
        }

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'task_number' => ['required', 'integer', 'in:1,2,3'],
            'attachment' => ['required', 'file', 'mimes:pdf,doc,docx,txt,zip,rar', 'max:20480'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($course->is_hidden) {
            abort(404);
        }

        $enrolledIds = $store->getStudentCourseIds((int) $request->user()->id);

        if (! in_array((int) $course->id, $enrolledIds, true)) {
            abort(403);
        }

        $learningState = $store->getLearningStateForStudent((int) $request->user()->id);
        $courseState = $learningState[(string) $course->id] ?? [];
        $taskNumber = (int) $data['task_number'];

        if ((bool) ($courseState['tasks_submitted'][$taskNumber - 1] ?? false)) {
            return redirect()->route('panel.alumno.course', ['course' => $course->id])
                ->with('success', 'Esta tarea ya fue entregada. No se puede deshacer ni reenviar.');
        }

        $path = $request->file('attachment')->store(
            'task-submissions/'.$request->user()->id.'/'.$course->id,
            'public'
        );

        $store->submitTaskWithEvidence(
            (int) $request->user()->id,
            (int) $course->id,
            $taskNumber,
            $path,
            (string) $request->file('attachment')->getClientOriginalName(),
            (string) ($data['observations'] ?? '')
        );

        return redirect()->route('panel.alumno.course', ['course' => $course->id])
            ->with('success', 'Tarea enviada correctamente. (+10%). Recuerda: una vez enviada no se puede deshacer.');
    }

    public function addMaterialUrl(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:140'],
            'url' => ['required', 'url', 'max:2000'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->addMaterialUrl((int) $course->id, (int) $request->user()->id, $data['title'], $data['url']);

        return back()->with('success', 'Material (URL/PDF online) añadido.');
    }

    public function addMaterialPdf(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:140'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf,application/x-pdf', 'max:51200'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $path = $request->file('pdf')->store('course-materials', 'public');
        $url = Storage::url($path);

        $store->addMaterialFile((int) $course->id, (int) $request->user()->id, $data['title'], $path, $url);

        return back()->with('success', 'PDF subido correctamente.');
    }

    public function deleteMaterial(Request $request, int $courseId, int $materialId, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $course = Course::query()->findOrFail($courseId);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->removeMaterial($courseId, $materialId);

        return back()->with('success', 'Material eliminado.');
    }

    public function updateCourseTasks(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'tasks' => ['required', 'array', 'size:3'],
            'tasks.*.title' => ['required', 'string', 'max:140'],
            'tasks.*.description' => ['nullable', 'string', 'max:2000'],
            'tasks.*.is_visible' => ['nullable', 'boolean'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->setCourseTaskDefinitions((int) $course->id, $data['tasks']);

        return back()->with('success', 'Tareas del curso actualizadas.');
    }

    public function updateExamDefinition(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'deadline' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->setCourseExamDefinition(
            (int) $course->id,
            $data['title'],
            (string) ($data['description'] ?? ''),
            (bool) $data['is_active'],
            $data['deadline'] ?? null
        );

        return back()->with('success', 'Evaluación final actualizada.');
    }

    public function gradeTask(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'task_number' => ['required', 'integer', 'in:1,2,3'],
            'grade' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->setTaskGrade(
            (int) $data['student_id'],
            (int) $course->id,
            (int) $data['task_number'],
            isset($data['grade']) ? (float) $data['grade'] : null
        );

        return back()->with('success', 'Calificación de tarea guardada.');
    }

    public function setExamResult(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'passed' => ['required', 'boolean'],
            'grade' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->setExamResult(
            (int) $data['student_id'],
            (int) $course->id,
            (bool) $data['passed'],
            isset($data['grade']) ? (float) $data['grade'] : null
        );

        return back()->with('success', 'Resultado de evaluación final actualizado.');
    }

    public function toggleCourseVisibility(Request $request): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'is_hidden' => ['required', 'boolean'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $course->is_hidden = (bool) $data['is_hidden'];
        $course->save();

        return back()->with('success', 'Visibilidad del curso actualizada.');
    }

    public function addAnnouncement(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:2000'],
            'type' => ['nullable', 'in:general,exam_date,center_info'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->addAnnouncement(
            (int) $course->id,
            (int) $request->user()->id,
            $data['title'],
            $data['body'],
            (string) ($data['type'] ?? 'general')
        );

        return back()->with('success', 'Anuncio publicado.');
    }

    public function deleteThread(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'thread_id' => ['required', 'string', 'max:100'],
        ]);

        $store->removeThread($data['thread_id']);

        return back()->with('success', 'Hilo eliminado correctamente.');
    }

    public function deleteAnnouncement(Request $request, int $announcementId, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);
        $store->removeAnnouncement($announcementId);

        return back()->with('success', 'Anuncio eliminado.');
    }

    public function sendMessage(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $data = $request->validate([
            'to_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'thread_id' => ['nullable', 'string', 'max:100'],
            'reply_to_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:2000'],
            'is_private' => ['required', 'boolean'],
        ]);

        $from = $request->user();
        $to = User::query()->findOrFail($data['to_id']);

        if ($from->role === 'alumno' && ! in_array($to->role, ['profesor', 'admin'], true)) {
            abort(403);
        }

        $store->addMessageWithThread(
            (int) $from->id,
            (int) $to->id,
            isset($data['course_id']) ? (int) $data['course_id'] : null,
            $data['subject'],
            $data['body'],
            $data['thread_id'] ?? null,
            isset($data['reply_to_id']) ? (int) $data['reply_to_id'] : null,
            (bool) $data['is_private']
        );

        return back()->with('success', 'Mensaje enviado.');
    }

    public function adminCreateUser(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:alumno,profesor,admin'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ]);

        User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'avatar' => 'default-avatar.png',
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function adminUpdateUser(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:alumno,profesor,admin'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);

        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->save();

        return back()->with('success', 'Usuario actualizado.');
    }

    public function adminDeleteUser(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->findOrFail($data['user_id']);

        if ((int) $user->id === (int) $request->user()->id) {
            return back()->with('success', 'No puedes eliminar tu propio usuario administrador desde este panel.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    public function adminDeleteCourse(Request $request): RedirectResponse
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'confirm' => ['required', 'accepted'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);
        $course->delete();

        return back()->with('success', 'Curso eliminado del catálogo.');
    }

    public function createCourse(Request $request): RedirectResponse
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Solo administradores pueden crear cursos');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'long_description' => ['required', 'string', 'max:2000'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('courses', 'public');
        }

        $course = Course::create([
            'title' => $validated['title'],
            'short_description' => $validated['short_description'],
            'long_description' => $validated['long_description'],
            'duration_hours' => $validated['duration_hours'],
            'category_id' => $validated['category_id'],
            'teacher_id' => $request->user()->id,
            'is_hidden' => true,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('panel.profesor.course', $course)->with('success', 'Curso creado exitosamente.');
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        if ($request->user()->role !== 'profesor' && $request->user()->role !== 'admin') {
            abort(403);
        }

        if ($course->teacher_id !== $request->user()->id && $request->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'long_description' => ['required', 'string', 'max:2000'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($course->image_path) {
                Storage::disk('public')->delete($course->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('courses', 'public');
        }

        unset($validated['image']);
        $course->update($validated);

        return back()->with('success', 'Curso actualizado exitosamente.');
    }
}
