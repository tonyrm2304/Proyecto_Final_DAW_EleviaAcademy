<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    enrolledCourses: {
        type: Array,
        default: () => [],
    },
    progress: {
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
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const messageForm = useForm({
    to_id: props.teacherContacts[0]?.id ?? '',
    course_id: props.enrolledCourses[0]?.id ?? '',
    subject: '',
    body: '',
});

const avgProgress = computed(() => {
    if (props.enrolledCourses.length === 0) return 0;

    const total = props.enrolledCourses.reduce((acc, course) => {
        const value = Number(props.progress[String(course.id)] ?? 0);
        return acc + value;
    }, 0);

    return Math.round(total / props.enrolledCourses.length);
});

const progressForCourse = (courseId) => Number(props.progress[String(courseId)] ?? 0);
const materialsForCourse = (courseId) => props.materialsByCourse[String(courseId)] ?? [];

const submitMessage = () => {
    messageForm.post(route('panel.messages.send'), {
        preserveScroll: true,
        onSuccess: () => messageForm.reset('subject', 'body'),
    });
};
</script>

<template>
    <Head title="Panel Alumno" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Panel Alumno</h2>
        </template>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ successMessage }}
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Cursos matriculados</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ enrolledCourses.length }}</p>
                </article>
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Progreso medio</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ avgProgress }}%</p>
                </article>
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Mensajes recibidos</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ messages.length }}</p>
                </article>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-2xl font-bold text-white">Mis cursos matriculados</h3>
                <p class="mt-2 text-slate-300">Por defecto tienes 4 cursos asignados. El profesorado/superadmin puede añadir o quitar cuando lo necesites.</p>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <article
                        v-for="course in enrolledCourses"
                        :key="course.id"
                        class="rounded-xl border border-slate-700 bg-slate-800/80 p-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <h4 class="text-lg font-bold text-white">{{ course.title }}</h4>
                            <span class="rounded-full bg-cyan-500/20 px-2 py-1 text-xs font-semibold text-cyan-300">
                                {{ progressForCourse(course.id) }}%
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-slate-300">{{ course.short_description }}</p>

                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-700">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500"
                                :style="{ width: `${progressForCourse(course.id)}%` }"
                            />
                        </div>

                        <p class="mt-4 text-xs uppercase tracking-wider text-slate-400">Contenido del curso</p>
                        <ul class="mt-2 space-y-2">
                            <li
                                v-for="material in materialsForCourse(course.id)"
                                :key="material.id"
                                class="flex items-center justify-between gap-3 rounded-lg border border-slate-700 bg-slate-900/80 px-3 py-2"
                            >
                                <span class="text-sm text-slate-100">{{ material.title }}</span>
                                <a
                                    :href="material.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-xs font-semibold text-cyan-300 hover:text-cyan-200"
                                >
                                    Abrir
                                </a>
                            </li>
                            <li v-if="materialsForCourse(course.id).length === 0" class="text-sm text-slate-400">
                                Aún no hay materiales publicados para este curso.
                            </li>
                        </ul>
                    </article>
                </div>
            </div>

            <div class="mt-8 grid gap-6 lg:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Tablón de anuncios</h3>
                    <ul class="mt-4 space-y-3">
                        <li
                            v-for="announcement in announcements"
                            :key="announcement.id"
                            class="rounded-xl border border-slate-700 bg-slate-800/80 p-4"
                        >
                            <p class="font-semibold text-cyan-300">{{ announcement.title }}</p>
                            <p class="mt-1 text-sm text-slate-200">{{ announcement.body }}</p>
                            <p class="mt-2 text-xs text-slate-400">Por: {{ announcement.author_name }}</p>
                            <p class="mt-2 text-xs text-slate-400">{{ new Date(announcement.created_at).toLocaleString() }}</p>
                        </li>
                        <li v-if="announcements.length === 0" class="text-sm text-slate-400">
                            No hay anuncios por ahora.
                        </li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Mensajería con profesorado</h3>

                    <form class="mt-4 space-y-3" @submit.prevent="submitMessage">
                        <select v-model="messageForm.to_id" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Selecciona destinatario</option>
                            <option v-for="teacher in teacherContacts" :key="teacher.id" :value="teacher.id">
                                {{ teacher.name }}
                            </option>
                        </select>

                        <select v-model="messageForm.course_id" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso relacionado</option>
                            <option v-for="course in enrolledCourses" :key="course.id" :value="course.id">
                                {{ course.title }}
                            </option>
                        </select>

                        <input
                            v-model="messageForm.subject"
                            type="text"
                            placeholder="Asunto"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100"
                        >

                        <textarea
                            v-model="messageForm.body"
                            rows="4"
                            placeholder="Escribe tu mensaje"
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100"
                        />

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white"
                            :disabled="messageForm.processing"
                        >
                            Enviar mensaje
                        </button>
                    </form>

                    <h4 class="mt-6 text-sm font-semibold uppercase tracking-wider text-slate-400">Bandeja de entrada</h4>
                    <ul class="mt-2 space-y-2">
                        <li
                            v-for="message in messages"
                            :key="message.id"
                            class="rounded-lg border border-slate-700 bg-slate-800/80 p-3"
                        >
                            <p class="font-semibold text-slate-100">{{ message.subject }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ message.body }}</p>
                            <p class="mt-2 text-xs text-slate-400">De: {{ message.from_name }}</p>
                            <p class="mt-2 text-xs text-slate-400">{{ new Date(message.created_at).toLocaleString() }}</p>
                        </li>
                        <li v-if="messages.length === 0" class="text-sm text-slate-400">
                            No has recibido mensajes todavía.
                        </li>
                    </ul>
                </article>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
