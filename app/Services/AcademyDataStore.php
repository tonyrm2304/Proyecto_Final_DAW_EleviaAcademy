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

        $this->writeData($data);
    }

    public function setProgress(int $studentId, int $courseId, int $value): void
    {
        $data = $this->readData();
        $studentKey = (string) $studentId;
        $courseKey = (string) $courseId;

        $data['progress'][$studentKey] ??= [];
        $data['progress'][$studentKey][$courseKey] = max(0, min(100, $value));

        $this->writeData($data);
    }

    public function getProgressForStudent(int $studentId): array
    {
        $data = $this->readData();
        $studentKey = (string) $studentId;

        return $data['progress'][$studentKey] ?? [];
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

    public function addAnnouncement(int $courseId, int $authorId, string $title, string $body): void
    {
        $data = $this->readData();
        $id = $this->nextCounter($data, 'announcement');

        $data['announcements'][] = [
            'id' => $id,
            'course_id' => $courseId,
            'author_id' => $authorId,
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
        $data = $this->readData();
        $id = $this->nextCounter($data, 'message');

        $data['messages'][] = [
            'id' => $id,
            'from_id' => $fromId,
            'to_id' => $toId,
            'course_id' => $courseId,
            'subject' => $subject,
            'body' => $body,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

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
}
