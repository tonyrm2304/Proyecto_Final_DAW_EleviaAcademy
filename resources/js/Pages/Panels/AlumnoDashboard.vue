<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getCourseImage } from '@/utils/courseImages';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    enrolledCourses: { type: Array, default: () => [] },
    progress: { type: Object, default: () => ({}) },
    learningState: { type: Object, default: () => ({}) },
    announcements: { type: Array, default: () => [] },
    teacherDirectory: { type: Array, default: () => [] },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const avgProgress = computed(() => {
    if (props.enrolledCourses.length === 0) return 0;

    const total = props.enrolledCourses.reduce((acc, course) => {
        const value = Number(props.progress[String(course.id)] ?? 0);
        return acc + value;
    }, 0);

    return Math.round(total / props.enrolledCourses.length);
});

const progressForCourse = (courseId) => Number(props.progress[String(courseId)] ?? 0);

const submittedTasksCount = (courseId) => {
    const tasks = props.learningState[String(courseId)]?.tasks_submitted ?? [false, false, false];
    return tasks.filter(Boolean).length;
};

const courseImage = (course) => getCourseImage(course);
</script>

<template>
    <Head title="Mi Panel Alumno" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">Mi Panel Alumno</h2>
        </template>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ successMessage }}
            </div>

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Progreso medio</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ avgProgress }}%</p>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Tablón de anuncios</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ announcements.length }}</p>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Mensajería</p>
                    <Link :href="route('panel.messages')" class="mt-2 inline-flex rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                        Abrir mensajes
                    </Link>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Mi perfil</p>
                    <Link :href="route('profile.edit')" class="mt-2 inline-flex rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-500">
                        Ir a mi perfil
                    </Link>
                </article>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-2xl font-bold text-white">Mis cursos matriculados</h3>
                <p class="mt-2 text-slate-300">Haz clic en un curso para ver su contenido completo, sus 3 tareas y calificaciones.</p>

                <div class="mt-6 grid gap-5 lg:grid-cols-2">
                    <article v-for="course in enrolledCourses" :key="course.id" class="flex h-full flex-col overflow-hidden rounded-xl border border-slate-700 bg-slate-800/80">
                        <img :src="courseImage(course)" :alt="course.title" class="h-44 w-full object-cover" @error="$event.target.src = '/imagenes/logo/Logo_EA.jpg'">

                        <div class="flex flex-1 flex-col p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h4 class="text-lg font-bold text-white">{{ course.title }}</h4>
                                <span class="rounded-full bg-cyan-500/20 px-2 py-1 text-xs font-semibold text-cyan-300">
                                    {{ progressForCourse(course.id) }}%
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-slate-300">{{ course.short_description }}</p>
                            <p class="mt-2 text-xs text-slate-400">Tareas entregadas: {{ submittedTasksCount(course.id) }}/3</p>

                            <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500" :style="{ width: `${progressForCourse(course.id)}%` }" />
                            </div>

                            <Link :href="route('panel.alumno.course', course.id)" class="mt-auto inline-flex rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">
                                Entrar al curso
                            </Link>
                        </div>
                    </article>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">Tablón de anuncios</h3>
                <ul class="mt-4 space-y-3">
                    <li v-for="announcement in announcements" :key="announcement.id" class="rounded-xl border border-slate-700 bg-slate-800/80 p-4">
                        <p class="font-semibold text-cyan-300">{{ announcement.title }}</p>
                        <p class="mt-1 text-sm text-slate-200">{{ announcement.body }}</p>
                        <p class="mt-2 text-xs text-slate-400">Por: {{ announcement.author_name }}</p>
                    </li>
                    <li v-if="announcements.length === 0" class="text-sm text-slate-400">No hay anuncios por ahora.</li>
                </ul>

                <h4 class="mt-6 text-sm font-semibold uppercase tracking-wider text-slate-400">Profesorado del centro</h4>
                <ul class="mt-3 space-y-2">
                    <li v-for="teacher in teacherDirectory" :key="teacher.id" class="flex items-center gap-3 rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2">
                        <img :src="teacher.avatar && teacher.avatar !== 'default-avatar.png' ? `/storage/${teacher.avatar}` : '/imagenes/logo/Logo_EA.jpg'" :alt="teacher.name" class="h-9 w-9 rounded-full object-cover">
                        <div>
                            <p class="text-sm font-semibold text-slate-100">{{ teacher.name }}</p>
                            <p class="text-xs text-slate-400">{{ teacher.email }}</p>
                        </div>
                    </li>
                </ul>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
