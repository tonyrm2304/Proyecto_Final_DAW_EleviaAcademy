<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    courses: {
        type: Array,
        default: () => [],
    },
    students: {
        type: Array,
        default: () => [],
    },
    enrollmentMap: {
        type: Object,
        default: () => ({}),
    },
    materialsByCourse: {
        type: Object,
        default: () => ({}),
    },
    announcements: {
        type: Array,
        default: () => [],
    },
    messages: {
        type: Array,
        default: () => [],
    },
    teacherContacts: {
        type: Array,
        default: () => [],
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const enrollmentForm = useForm({
    student_id: props.students[0]?.id ?? '',
    course_id: props.courses[0]?.id ?? '',
    action: 'add',
});

const progressForm = useForm({
    student_id: props.students[0]?.id ?? '',
    course_id: props.courses[0]?.id ?? '',
    progress: 20,
});

const materialUrlForm = useForm({
    course_id: props.courses[0]?.id ?? '',
    title: '',
    url: '',
});

const materialPdfForm = useForm({
    course_id: props.courses[0]?.id ?? '',
    title: '',
    pdf: null,
});

const announcementForm = useForm({
    course_id: props.courses[0]?.id ?? '',
    title: '',
    body: '',
});

const messageForm = useForm({
    to_id: props.students[0]?.id ?? '',
    course_id: props.courses[0]?.id ?? '',
    subject: '',
    body: '',
});

const materialsForCourse = (courseId) => props.materialsByCourse[String(courseId)] ?? [];

const isEnrolled = (studentId, courseId) => {
    const ids = props.enrollmentMap[String(studentId)] ?? [];
    return ids.map((id) => Number(id)).includes(Number(courseId));
};

const submitEnrollment = () => {
    enrollmentForm.post(route('panel.enrollment.update'), { preserveScroll: true });
};

const submitProgress = () => {
    progressForm.post(route('panel.progress.update'), { preserveScroll: true });
};

const submitMaterialUrl = () => {
    materialUrlForm.post(route('panel.materials.add-url'), {
        preserveScroll: true,
        onSuccess: () => materialUrlForm.reset('title', 'url'),
    });
};

const onPdfFileChange = (event) => {
    materialPdfForm.pdf = event.target.files?.[0] ?? null;
};

const submitMaterialPdf = () => {
    materialPdfForm.post(route('panel.materials.add-pdf'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => materialPdfForm.reset('title', 'pdf'),
    });
};

const deleteMaterial = (courseId, materialId) => {
    router.delete(route('panel.materials.delete', { courseId, materialId }), {
        preserveScroll: true,
    });
};

const submitAnnouncement = () => {
    announcementForm.post(route('panel.announcements.add'), {
        preserveScroll: true,
        onSuccess: () => announcementForm.reset('title', 'body'),
    });
};

const deleteAnnouncement = (announcementId) => {
    router.delete(route('panel.announcements.delete', { announcementId }), {
        preserveScroll: true,
    });
};

const submitMessage = () => {
    messageForm.post(route('panel.messages.send'), {
        preserveScroll: true,
        onSuccess: () => messageForm.reset('subject', 'body'),
    });
};
</script>

<template>
    <Head title="Panel Profesor" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                {{ isAdmin ? 'Panel Superadmin' : 'Panel Profesor' }}
            </h2>
        </template>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ successMessage }}
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Cursos gestionados</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ courses.length }}</p>
                </article>
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Alumnos activos</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ students.length }}</p>
                </article>
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Mensajes recibidos</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ messages.length }}</p>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Gestión de matrículas</h3>
                    <form class="mt-4 grid gap-3" @submit.prevent="submitEnrollment">
                        <select v-model="enrollmentForm.student_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Alumno</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                        </select>
                        <select v-model="enrollmentForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>
                        <select v-model="enrollmentForm.action" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option value="add">Añadir matrícula</option>
                            <option value="remove">Quitar matrícula</option>
                        </select>
                        <button type="submit" class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white">
                            Aplicar cambio
                        </button>
                    </form>

                    <div class="mt-6 overflow-auto rounded-lg border border-slate-700">
                        <table class="min-w-full text-left text-sm">
                            <thead class="bg-slate-800 text-slate-300">
                                <tr>
                                    <th class="px-3 py-2">Alumno</th>
                                    <th class="px-3 py-2">Curso</th>
                                    <th class="px-3 py-2">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 bg-slate-900/60 text-slate-200">
                                <tr v-for="student in students" :key="student.id">
                                    <td class="px-3 py-2">{{ student.name }}</td>
                                    <td class="px-3 py-2">
                                        {{ courses[0]?.title ?? 'Sin cursos' }}
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="rounded-full px-2 py-1 text-xs" :class="isEnrolled(student.id, courses[0]?.id) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300'">
                                            {{ isEnrolled(student.id, courses[0]?.id) ? 'Matriculado' : 'No matriculado' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Actualizar progreso</h3>
                    <form class="mt-4 grid gap-3" @submit.prevent="submitProgress">
                        <select v-model="progressForm.student_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Alumno</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                        </select>
                        <select v-model="progressForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>
                        <input v-model.number="progressForm.progress" type="number" min="0" max="100" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" placeholder="Progreso (0-100)">
                        <button type="submit" class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white">
                            Guardar progreso
                        </button>
                    </form>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Contenido real del curso</h3>
                    <p class="mt-2 text-sm text-slate-300">Puedes añadir enlaces a PDFs online o subir PDFs locales. Todo queda visible para el alumnado matriculado.</p>

                    <form class="mt-4 grid gap-3" @submit.prevent="submitMaterialUrl">
                        <select v-model="materialUrlForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>
                        <input v-model="materialUrlForm.title" type="text" placeholder="Título del material" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input v-model="materialUrlForm.url" type="url" placeholder="https://...pdf" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white">Añadir URL/PDF online</button>
                    </form>

                    <form class="mt-4 grid gap-3" @submit.prevent="submitMaterialPdf">
                        <select v-model="materialPdfForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>
                        <input v-model="materialPdfForm.title" type="text" placeholder="Título del PDF" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input type="file" accept="application/pdf" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" @change="onPdfFileChange">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 font-semibold text-white">Subir PDF</button>
                    </form>

                    <p class="mt-5 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                        Para añadir links manualmente en código (si quieres precargar), revisa el método
                        <strong>manualMaterialSeed()</strong> en <code>app/Services/AcademyDataStore.php</code>.
                    </p>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Materiales publicados</h3>
                    <div class="mt-4 space-y-4">
                        <div v-for="course in courses" :key="course.id" class="rounded-lg border border-slate-700 bg-slate-800/70 p-4">
                            <h4 class="font-semibold text-cyan-300">{{ course.title }}</h4>
                            <ul class="mt-2 space-y-2">
                                <li
                                    v-for="material in materialsForCourse(course.id)"
                                    :key="material.id"
                                    class="flex items-center justify-between rounded-md border border-slate-700 bg-slate-900/70 px-3 py-2"
                                >
                                    <a :href="material.url" target="_blank" rel="noopener" class="text-sm text-slate-100 hover:text-cyan-300">{{ material.title }}</a>
                                    <button type="button" class="text-xs text-rose-300 hover:text-rose-200" @click="deleteMaterial(course.id, material.id)">Eliminar</button>
                                </li>
                                <li v-if="materialsForCourse(course.id).length === 0" class="text-sm text-slate-400">Sin materiales aún.</li>
                            </ul>
                        </div>
                    </div>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Tablón de anuncios</h3>
                    <form class="mt-4 grid gap-3" @submit.prevent="submitAnnouncement">
                        <select v-model="announcementForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>
                        <input v-model="announcementForm.title" type="text" placeholder="Título del anuncio" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <textarea v-model="announcementForm.body" rows="3" placeholder="Mensaje para el tablón" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" />
                        <button type="submit" class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white">Publicar anuncio</button>
                    </form>

                    <ul class="mt-4 space-y-2">
                        <li v-for="announcement in announcements" :key="announcement.id" class="rounded-md border border-slate-700 bg-slate-800/80 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-cyan-300">{{ announcement.title }}</p>
                                    <p class="mt-1 text-sm text-slate-200">{{ announcement.body }}</p>
                                    <p class="mt-1 text-xs text-slate-400">Por: {{ announcement.author_name }}</p>
                                </div>
                                <button type="button" class="text-xs text-rose-300 hover:text-rose-200" @click="deleteAnnouncement(announcement.id)">Eliminar</button>
                            </div>
                        </li>
                        <li v-if="announcements.length === 0" class="text-sm text-slate-400">No hay anuncios todavía.</li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Mensajería</h3>
                    <form class="mt-4 grid gap-3" @submit.prevent="submitMessage">
                        <select v-model="messageForm.to_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Destinatario</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                        </select>
                        <select v-model="messageForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso relacionado</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>
                        <input v-model="messageForm.subject" type="text" placeholder="Asunto" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <textarea v-model="messageForm.body" rows="3" placeholder="Escribe el mensaje" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" />
                        <button type="submit" class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white">Enviar mensaje</button>
                    </form>

                    <h4 class="mt-5 text-sm uppercase tracking-wider text-slate-400">Bandeja de entrada</h4>
                    <ul class="mt-2 space-y-2">
                        <li v-for="message in messages" :key="message.id" class="rounded-md border border-slate-700 bg-slate-800/80 p-3">
                            <p class="font-semibold text-slate-100">{{ message.subject }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ message.body }}</p>
                            <p class="mt-1 text-xs text-slate-400">De: {{ message.from_name }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ new Date(message.created_at).toLocaleString() }}</p>
                        </li>
                        <li v-if="messages.length === 0" class="text-sm text-slate-400">No tienes mensajes por ahora.</li>
                    </ul>
                </article>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
