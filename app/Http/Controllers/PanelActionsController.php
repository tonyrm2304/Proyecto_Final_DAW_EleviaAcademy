<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Services\AcademyDataStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PanelActionsController extends Controller
{
    private function ensureTeacherOrAdmin(Request $request): void
    {
        if (! in_array($request->user()->role, ['profesor', 'admin'], true)) {
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
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->setProgress((int) $data['student_id'], (int) $data['course_id'], (int) $data['progress']);

        return back()->with('success', 'Progreso actualizado.');
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
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
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

    public function addAnnouncement(Request $request, AcademyDataStore $store): RedirectResponse
    {
        $this->ensureTeacherOrAdmin($request);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $course = Course::query()->findOrFail($data['course_id']);

        if ($request->user()->role === 'profesor' && (int) $course->teacher_id !== (int) $request->user()->id) {
            abort(403);
        }

        $store->addAnnouncement((int) $course->id, (int) $request->user()->id, $data['title'], $data['body']);

        return back()->with('success', 'Anuncio publicado.');
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
            'subject' => ['required', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $from = $request->user();
        $to = User::query()->findOrFail($data['to_id']);

        if ($from->role === 'alumno' && ! in_array($to->role, ['profesor', 'admin'], true)) {
            abort(403);
        }

        $store->addMessage(
            (int) $from->id,
            (int) $to->id,
            isset($data['course_id']) ? (int) $data['course_id'] : null,
            $data['subject'],
            $data['body']
        );

        return back()->with('success', 'Mensaje enviado.');
    }
}
