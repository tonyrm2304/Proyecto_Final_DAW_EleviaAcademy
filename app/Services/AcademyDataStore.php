<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AcademyDataStore
{
    private function filePath(): string
    {
        return storage_path('app/private/academy_data.json');
    }

    private function defaultData(): array
    {
        return [
            'enrollments' => [],
            'progress' => [],
            'learning' => [],
            'course_tasks' => [],
            'course_exams' => [],
            'materials' => [],
            'announcements' => [],
            'messages' => [],
            'counters' => [
                'announcement' => 1,
                'message' => 1,
                'material' => 1,
            ],
        ];
    }

    private function defaultTaskDefinitions(int $courseId): array
    {
        return [
            [
                'id' => 1,
                'course_id' => $courseId,
                'title' => 'Tarea 1: Actividad práctica inicial',
                'description' => 'Entrega introductoria para validar conceptos básicos del curso.',
                'is_visible' => true,
                'deadline' => null,
            ],
            [
                'id' => 2,
                'course_id' => $courseId,
                'title' => 'Tarea 2: Caso aplicado',
                'description' => 'Resolución guiada de un caso con entrega individual.',
                'is_visible' => true,
                'deadline' => null,
            ],
            [
                'id' => 3,
                'course_id' => $courseId,
                'title' => 'Tarea 3: Entrega final del módulo',
                'description' => 'Trabajo final corto para consolidar el bloque didáctico.',
                'is_visible' => true,
                'deadline' => null,
            ],
        ];
    }

    private function defaultExamDefinition(int $courseId): array
    {
        return [
            'course_id' => $courseId,
            'title' => 'Evaluación final',
            'description' => 'Prueba final del curso para validación de competencias.',
            'is_active' => true,
            'deadline' => null,
        ];
    }

    private function readData(): array
    {
        $path = $this->filePath();

        if (! file_exists($path)) {
            $this->writeData($this->defaultData());
        }

        $raw = file_get_contents($path);

        if ($raw === false || trim($raw) === '') {
            return $this->defaultData();
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return $this->defaultData();
        }

        return array_replace_recursive($this->defaultData(), $decoded);
    }

    private function writeData(array $data): void
    {
        $path = $this->filePath();
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function nextCounter(array &$data, string $name): int
    {
        $current = (int) ($data['counters'][$name] ?? 1);
        $data['counters'][$name] = $current + 1;

        return $current;
    }

    /**
     * ZONA DESTACADA PARA AÑADIR PDFs/LINKS MANUALMENTE (sin lorem ipsum):
     * - Puedes editar este array con materiales reales por curso.
     * - Se insertan automáticamente la primera vez que ese curso no tenga materiales.
     */
    private function manualMaterialSeed(): array
    {
        return [
            // Ejemplo:
            // [
            //     'course_id' => 1,
            //     'title' => 'Guía oficial Prompt Engineering',
            //     'type' => 'url',
            //     'url' => 'https://ejemplo.com/guia-prompt-engineering.pdf',
            // ],
        ];
    }

    public function seedMaterialsIfNeeded(): void
    {
        $data = $this->readData();
        $seed = $this->manualMaterialSeed();

        if ($seed === []) {
            return;
        }

        foreach ($seed as $item) {
            $courseId = (int) ($item['course_id'] ?? 0);
            if ($courseId <= 0) {
                continue;
            }

            $courseKey = (string) $courseId;
            $existing = $data['materials'][$courseKey] ?? [];

            $alreadyExists = collect($existing)->contains(function (array $material) use ($item) {
                return ($material['title'] ?? '') === ($item['title'] ?? '')
                    && ($material['url'] ?? '') === ($item['url'] ?? '');
            });

            if ($alreadyExists) {
                continue;
            }

            $materialId = $this->nextCounter($data, 'material');

            $data['materials'][$courseKey][] = [
                'id' => $materialId,
                'title' => (string) ($item['title'] ?? 'Material del curso'),
                'type' => (string) ($item['type'] ?? 'url'),
                'url' => (string) ($item['url'] ?? ''),
                'path' => null,
                'uploaded_by' => null,
                'created_at' => Carbon::now()->toIso8601String(),
            ];
        }

        $this->writeData($data);
    }

    public function ensureDefaultEnrollment(int $userId, Collection $visibleCourses): array
    {
        $data = $this->readData();
        $userKey = (string) $userId;

        if (! isset($data['enrollments'][$userKey]) || count($data['enrollments'][$userKey]) === 0) {
            $data['enrollments'][$userKey] = $visibleCourses
                ->take(4)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            foreach ($data['enrollments'][$userKey] as $courseId) {
                $this->ensureLearningStateInData($data, $userId, (int) $courseId);
            }

            $this->writeData($data);
        }

        return collect($data['enrollments'][$userKey] ?? [])
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function getEnrollmentMap(): array
    {
        return $this->readData()['enrollments'] ?? [];
    }

    public function getStudentCourseIds(int $userId): array
    {
        $map = $this->getEnrollmentMap();
        $userKey = (string) $userId;

        return collect($map[$userKey] ?? [])
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function addEnrollment(int $studentId, int $courseId): void
    {
        $data = $this->readData();
        $key = (string) $studentId;
        $current = collect($data['enrollments'][$key] ?? [])->map(fn ($id) => (int) $id);

        if (! $current->contains($courseId)) {
            $current->push($courseId);
        }

        $data['enrollments'][$key] = $current->values()->all();
        $this->ensureLearningStateInData($data, $studentId, $courseId);
        $this->recalculateProgressInData($data, $studentId, $courseId);
        $this->writeData($data);
    }

    public function removeEnrollment(int $studentId, int $courseId): void
    {
        $data = $this->readData();
        $key = (string) $studentId;
        $current = collect($data['enrollments'][$key] ?? [])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === $courseId)
            ->values();

        $data['enrollments'][$key] = $current->all();

        if (isset($data['progress'][$key][(string) $courseId])) {
            unset($data['progress'][$key][(string) $courseId]);
        }

        if (isset($data['learning'][$key][(string) $courseId])) {
            unset($data['learning'][$key][(string) $courseId]);
        }

        $this->writeData($data);
    }

    public function setProgress(int $studentId, int $courseId, int $value): void
    {
        $clamped = max(0, min(100, $value));

        $data = $this->readData();
        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;

        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $data['learning'][$studentKey][$courseKey]['content_completed'] = $clamped >= 50;

        $taskCompleted = $clamped >= 60 ? 1 : 0;
        $taskCompleted += $clamped >= 70 ? 1 : 0;
        $taskCompleted += $clamped >= 80 ? 1 : 0;

        $data['learning'][$studentKey][$courseKey]['tasks_submitted'] = [
            $taskCompleted >= 1,
            $taskCompleted >= 2,
            $taskCompleted >= 3,
        ];

        $data['learning'][$studentKey][$courseKey]['exam_passed'] = $clamped >= 100;
        $this->recalculateProgressInData($data, $studentId, $courseId);

        $this->writeData($data);
    }

    public function getProgressForStudent(int $studentId): array
    {
        $data = $this->readData();
        $studentKey = (string) $studentId;

        $courseIds = collect($data['enrollments'][$studentKey] ?? [])->map(fn ($id) => (int) $id)->all();

        foreach ($courseIds as $courseId) {
            $this->ensureLearningStateInData($data, $studentId, $courseId);
            $this->recalculateProgressInData($data, $studentId, $courseId);
        }

        $this->writeData($data);

        return $data['progress'][$studentKey] ?? [];
    }

    public function getLearningStateForStudent(int $studentId): array
    {
        $data = $this->readData();
        $studentKey = (string) $studentId;

        return $data['learning'][$studentKey] ?? [];
    }

    public function completeCourseContent(int $studentId, int $courseId): void
    {
        $data = $this->readData();
        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;
        $data['learning'][$studentKey][$courseKey]['content_completed'] = true;

        $this->recalculateProgressInData($data, $studentId, $courseId);
        $this->writeData($data);
    }

    public function submitTask(int $studentId, int $courseId, int $taskNumber): void
    {
        if (! in_array($taskNumber, [1, 2, 3], true)) {
            return;
        }

        $data = $this->readData();
        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;
        $index = $taskNumber - 1;

        $data['learning'][$studentKey][$courseKey]['tasks_submitted'][$index] = true;

        $this->recalculateProgressInData($data, $studentId, $courseId);
        $this->writeData($data);
    }

    public function setTaskGrade(int $studentId, int $courseId, int $taskNumber, ?float $grade): void
    {
        if (! in_array($taskNumber, [1, 2, 3], true)) {
            return;
        }

        $data = $this->readData();
        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;

        $data['learning'][$studentKey][$courseKey]['grades']['task'.$taskNumber] = $grade;
        $this->writeData($data);
    }

    public function setExamResult(int $studentId, int $courseId, bool $passed, ?float $grade = null): void
    {
        $data = $this->readData();
        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;

        $data['learning'][$studentKey][$courseKey]['exam_passed'] = $passed;
        $data['learning'][$studentKey][$courseKey]['grades']['final'] = $grade;

        $this->recalculateProgressInData($data, $studentId, $courseId);
        $this->writeData($data);
    }

    public function getCourseTaskDefinitions(int $courseId): array
    {
        $data = $this->readData();
        $courseKey = (string) $courseId;

        if (! isset($data['course_tasks'][$courseKey]) || count($data['course_tasks'][$courseKey]) !== 3) {
            $data['course_tasks'][$courseKey] = $this->defaultTaskDefinitions($courseId);
            $this->writeData($data);
        }

        return $data['course_tasks'][$courseKey] ?? $this->defaultTaskDefinitions($courseId);
    }

    public function setCourseTaskDefinitions(int $courseId, array $tasks): void
    {
        $normalized = collect($tasks)
            ->take(3)
            ->values()
            ->map(function (array $task, int $index) use ($courseId) {
                return [
                    'id' => $index + 1,
                    'course_id' => $courseId,
                    'title' => (string) ($task['title'] ?? 'Tarea '.($index + 1)),
                    'description' => (string) ($task['description'] ?? ''),
                    'is_visible' => (bool) ($task['is_visible'] ?? true),
                    'deadline' => $task['deadline'] ?? null,
                ];
            })
            ->all();

        while (count($normalized) < 3) {
            $next = count($normalized) + 1;
            $normalized[] = [
                'id' => $next,
                'course_id' => $courseId,
                'title' => 'Tarea '.$next,
                'description' => '',
                'is_visible' => true,
                'deadline' => null,
            ];
        }

        $data = $this->readData();
        $data['course_tasks'][(string) $courseId] = $normalized;
        $this->writeData($data);
    }

    public function getCourseExamDefinition(int $courseId): array
    {
        $data = $this->readData();
        $courseKey = (string) $courseId;

        if (! isset($data['course_exams'][$courseKey])) {
            $data['course_exams'][$courseKey] = $this->defaultExamDefinition($courseId);
            $this->writeData($data);
        }

        return $data['course_exams'][$courseKey] ?? $this->defaultExamDefinition($courseId);
    }

    public function setCourseExamDefinition(int $courseId, string $title, string $description, bool $isActive, ?string $deadline = null): void
    {
        $data = $this->readData();
        $data['course_exams'][(string) $courseId] = [
            'course_id' => $courseId,
            'title' => $title,
            'description' => $description,
            'is_active' => $isActive,
            'deadline' => $deadline,
        ];
        $this->writeData($data);
    }

    public function getMaterialsForCourses(array $courseIds): array
    {
        $data = $this->readData();
        $materials = $data['materials'] ?? [];
        $result = [];

        foreach ($courseIds as $courseId) {
            $result[(string) $courseId] = $materials[(string) $courseId] ?? [];
        }

        return $result;
    }

    public function addMaterialUrl(int $courseId, int $uploaderId, string $title, string $url): void
    {
        $data = $this->readData();
        $courseKey = (string) $courseId;
        $materialId = $this->nextCounter($data, 'material');

        $data['materials'][$courseKey][] = [
            'id' => $materialId,
            'title' => $title,
            'type' => 'url',
            'url' => $url,
            'path' => null,
            'uploaded_by' => $uploaderId,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $this->writeData($data);
    }

    public function addMaterialFile(int $courseId, int $uploaderId, string $title, string $relativePath, string $publicUrl): void
    {
        $data = $this->readData();
        $courseKey = (string) $courseId;
        $materialId = $this->nextCounter($data, 'material');

        $data['materials'][$courseKey][] = [
            'id' => $materialId,
            'title' => $title,
            'type' => 'pdf',
            'url' => $publicUrl,
            'path' => $relativePath,
            'uploaded_by' => $uploaderId,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $this->writeData($data);
    }

    public function removeMaterial(int $courseId, int $materialId): void
    {
        $data = $this->readData();
        $courseKey = (string) $courseId;

        $data['materials'][$courseKey] = collect($data['materials'][$courseKey] ?? [])
            ->reject(fn (array $material) => (int) ($material['id'] ?? 0) === $materialId)
            ->values()
            ->all();

        $this->writeData($data);
    }

    public function getAnnouncementsForCourses(array $courseIds): array
    {
        $ids = collect($courseIds)->map(fn ($id) => (int) $id)->all();

        return collect($this->readData()['announcements'] ?? [])
            ->filter(function (array $announcement) use ($ids) {
                $courseId = (int) ($announcement['course_id'] ?? 0);

                return $courseId === 0 || in_array($courseId, $ids, true);
            })
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function getAnnouncementsForTeacher(array $courseIds): array
    {
        $ids = collect($courseIds)->map(fn ($id) => (int) $id)->all();

        return collect($this->readData()['announcements'] ?? [])
            ->filter(fn (array $announcement) => in_array((int) ($announcement['course_id'] ?? 0), $ids, true))
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function addAnnouncement(int $courseId, int $authorId, string $title, string $body, string $type = 'general'): void
    {
        $data = $this->readData();
        $id = $this->nextCounter($data, 'announcement');

        $data['announcements'][] = [
            'id' => $id,
            'course_id' => $courseId,
            'author_id' => $authorId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $this->writeData($data);
    }

    public function removeAnnouncement(int $announcementId): void
    {
        $data = $this->readData();
        $data['announcements'] = collect($data['announcements'] ?? [])
            ->reject(fn (array $item) => (int) ($item['id'] ?? 0) === $announcementId)
            ->values()
            ->all();
        $this->writeData($data);
    }

    public function addMessage(int $fromId, int $toId, ?int $courseId, string $subject, string $body): void
    {
        $this->addMessageWithThread($fromId, $toId, $courseId, $subject, $body);
    }

    public function addMessageWithThread(int $fromId, int $toId, ?int $courseId, string $subject, string $body, ?string $threadId = null, ?int $replyToId = null, bool $isPrivate = false): void
    {
        $data = $this->readData();
        $id = $this->nextCounter($data, 'message');

        $finalThreadId = $threadId ?: $this->buildThreadId($fromId, $toId, $courseId);

        $data['messages'][] = [
            'id' => $id,
            'from_id' => $fromId,
            'to_id' => $toId,
            'course_id' => $courseId,
            'thread_id' => $finalThreadId,
            'reply_to_id' => $replyToId,
            'subject' => $subject,
            'body' => $body,
            'is_private' => $isPrivate,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $this->writeData($data);
    }

    public function threadMessagesForUser(int $userId, string $threadId): array
    {
        return collect($this->readData()['messages'] ?? [])
            ->filter(function (array $msg) use ($userId, $threadId) {
                $isParticipant = (int) ($msg['from_id'] ?? 0) === $userId || (int) ($msg['to_id'] ?? 0) === $userId;
                $isPrivate = (bool) ($msg['is_private'] ?? false);
                $isSameThread = (string) ($msg['thread_id'] ?? '') === $threadId;

                // Si es privado, solo pueden verlo los participantes
                if ($isPrivate) {
                    return $isParticipant && $isSameThread;
                }

                // Si no es privado, solo pueden verlo los participantes (miembros del curso/hilo)
                return $isParticipant && $isSameThread;
            })
            ->sortBy('created_at')
            ->values()
            ->all();
    }

    public function allMessagesForUser(int $userId): array
    {
        return collect($this->readData()['messages'] ?? [])
            ->filter(fn (array $msg) => (int) ($msg['from_id'] ?? 0) === $userId || (int) ($msg['to_id'] ?? 0) === $userId)
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function removeThread(string $threadId): void
    {
        $data = $this->readData();

        $data['messages'] = collect($data['messages'] ?? [])
            ->reject(fn (array $msg) => (string) ($msg['thread_id'] ?? '') === $threadId)
            ->values()
            ->all();

        $this->writeData($data);
    }

    public function inboxForUser(int $userId): array
    {
        return collect($this->readData()['messages'] ?? [])
            ->filter(fn (array $msg) => (int) ($msg['to_id'] ?? 0) === $userId)
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function outboxForUser(int $userId): array
    {
        return collect($this->readData()['messages'] ?? [])
            ->filter(fn (array $msg) => (int) ($msg['from_id'] ?? 0) === $userId)
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    public function visibleCoursesForRole(?int $teacherId, bool $isAdmin): Collection
    {
        $query = Course::query()
            ->with(['category:id,name', 'teacher:id,name'])
            ->where('is_hidden', false);

        if (! $isAdmin && $teacherId !== null) {
            $query->where('teacher_id', $teacherId);
        }

        return $query->orderBy('title')->get();
    }

    public function visibleAndHiddenCoursesForRole(?int $teacherId, bool $isAdmin): Collection
    {
        $query = Course::query()
            ->with(['category:id,name', 'teacher:id,name']);

        if (! $isAdmin && $teacherId !== null) {
            $query->where('teacher_id', $teacherId);
        }

        return $query->orderBy('title')->get();
    }

    private function buildThreadId(int $fromId, int $toId, ?int $courseId): string
    {
        $pair = [$fromId, $toId];
        sort($pair);

        return implode('-', [
            't',
            $pair[0],
            $pair[1],
            $courseId ?? 0,
        ]);
    }

    private function ensureLearningStateInData(array &$data, int $studentId, int $courseId): void
    {
        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;

        $data['learning'][$studentKey] ??= [];
        $data['learning'][$studentKey][$courseKey] ??= [
            'content_completed' => false,
            'tasks_submitted' => [false, false, false],
            'task_submissions' => [
                'task1' => null,
                'task2' => null,
                'task3' => null,
            ],
            'exam_passed' => false,
            'grades' => [
                'task1' => null,
                'task2' => null,
                'task3' => null,
                'final' => null,
            ],
        ];

        $data['learning'][$studentKey][$courseKey]['task_submissions'] ??= [];

        for ($taskNumber = 1; $taskNumber <= 3; $taskNumber++) {
            $key = 'task'.$taskNumber;
            if (! array_key_exists($key, $data['learning'][$studentKey][$courseKey]['task_submissions'])) {
                $data['learning'][$studentKey][$courseKey]['task_submissions'][$key] = null;
            }
        }
    }

    public function submitTaskWithEvidence(int $studentId, int $courseId, int $taskNumber, string $filePath, string $originalName, string $observations = ''): void
    {
        if (! in_array($taskNumber, [1, 2, 3], true)) {
            return;
        }

        $data = $this->readData();
        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;
        $index = $taskNumber - 1;
        $taskKey = 'task'.$taskNumber;

        $data['learning'][$studentKey][$courseKey]['tasks_submitted'][$index] = true;
        $data['learning'][$studentKey][$courseKey]['task_submissions'][$taskKey] = [
            'path' => $filePath,
            'file_name' => $originalName,
            'url' => '/storage/'.$filePath,
            'observations' => $observations,
            'submitted_at' => Carbon::now()->toIso8601String(),
        ];

        $this->recalculateProgressInData($data, $studentId, $courseId);
        $this->writeData($data);
    }

    private function recalculateProgressInData(array &$data, int $studentId, int $courseId): void
    {
        $this->ensureLearningStateInData($data, $studentId, $courseId);

        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;
        $state = $data['learning'][$studentKey][$courseKey];

        $contentScore = ! empty($state['content_completed']) ? 50 : 0;

        $tasks = collect($state['tasks_submitted'] ?? [false, false, false])
            ->map(fn ($value) => (bool) $value)
            ->take(3)
            ->values()
            ->all();

        while (count($tasks) < 3) {
            $tasks[] = false;
        }

        $taskScore = collect($tasks)->filter()->count() * 10;
        $examScore = ! empty($state['exam_passed']) ? 20 : 0;

        $data['progress'][$studentKey] ??= [];
        $data['progress'][$studentKey][$courseKey] = max(0, min(100, $contentScore + $taskScore + $examScore));
    }
}
