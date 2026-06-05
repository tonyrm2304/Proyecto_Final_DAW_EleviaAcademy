<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getCourseImage } from '@/utils/courseImages';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    materials: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
    exam: { type: Object, default: () => ({}) },
    progress: { type: Number, default: 0 },
    state: { type: Object, default: null },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const contentForm = useForm({ course_id: props.course.id });

const submitContentCompleted = () => {
    contentForm.post(route('panel.alumno.complete-content'), { preserveScroll: true });
};

const taskSubmitted = (taskNumber) => Boolean(props.state?.tasks_submitted?.[taskNumber - 1]);
const taskSubmission = (taskNumber) => props.state?.task_submissions?.[`task${taskNumber}`] ?? null;
const finalApproved = computed(() => Boolean(props.state?.exam_passed));

const courseImage = computed(() => getCourseImage(props.course));
</script>

<template>
    <Head :title="`Curso · ${course.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-white">{{ course.title }}</h2>
                <Link :href="route('panel.alumno')" class="rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">
                    Volver al panel
                </Link>
            </div>
        </template>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ successMessage }}
            </div>

            <img :src="courseImage" :alt="course.title" class="h-64 w-full rounded-2xl object-cover" @error="$event.target.src = '/imagenes/logo/Logo_EA.jpg'">

            <div class="mt-6 grid gap-6 md:grid-cols-3">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Progreso del curso</p>
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ progress }}%</p>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-700">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-cyan-500" :style="{ width: `${progress}%` }" />
                    </div>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Contenido didáctico</p>
                    <p class="mt-2 text-sm text-slate-300">Al visualizar todo el contenido se suma automáticamente el 50%.</p>
                    <button class="mt-3 rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-500" @click="submitContentCompleted" :disabled="Boolean(state?.content_completed)">
                        {{ state?.content_completed ? 'Contenido completado' : 'Marcar contenido visualizado (+50%)' }}
                    </button>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Evaluación final</p>
                    <p class="mt-2 text-sm text-slate-300">{{ exam?.title }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ finalApproved ? 'Aprobada: +20% aplicado' : 'Pendiente de aprobación por profesorado' }}</p>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Contenido del curso</h3>
                    <ul class="mt-4 space-y-2">
                        <li v-for="material in materials" :key="material.id" class="flex items-center justify-between gap-3 rounded-lg border border-slate-700 bg-slate-800/80 px-3 py-2">
                            <span class="text-sm text-slate-100">{{ material.title }}</span>
                            <a :href="material.url" target="_blank" rel="noopener" class="text-xs font-semibold text-cyan-300 hover:text-cyan-200">Abrir</a>
                        </li>
                        <li v-if="materials.length === 0" class="text-sm text-slate-400">No hay material publicado todavía.</li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Tareas obligatorias</h3>
                    <ul class="mt-4 space-y-3">
                        <li v-for="task in tasks" :key="task.id" class="rounded-xl border border-slate-700 bg-slate-800/80 p-4">
                            <p class="font-semibold text-cyan-300">{{ task.title }}</p>
                            <p class="mt-1 text-sm text-slate-300">{{ task.description }}</p>

                            <div class="mt-3 flex items-center justify-between">
                                <p class="text-xs text-slate-400">Estado: {{ taskSubmitted(task.id) ? 'Entregada' : 'Pendiente' }}</p>
                                <Link
                                    :href="route('panel.alumno.task.submit-view', { course: course.id, taskNumber: task.id })"
                                    class="rounded-lg border border-cyan-500/40 px-3 py-1.5 text-xs font-semibold text-cyan-300 hover:bg-cyan-500/10"
                                >
                                    {{ taskSubmitted(task.id) ? 'Ver entrega enviada' : 'Entregar tarea (+10%)' }}
                                </Link>
                            </div>

                            <div v-if="taskSubmission(task.id)" class="mt-2 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2">
                                <p class="text-xs text-emerald-200">
                                    Archivo enviado: {{ taskSubmission(task.id)?.file_name }}
                                </p>
                                <p v-if="taskSubmission(task.id)?.observations" class="mt-1 text-xs text-slate-300">
                                    Observaciones: {{ taskSubmission(task.id)?.observations }}
                                </p>
                            </div>

                            <p class="mt-2 text-xs text-slate-400">Calificación: {{ state?.grades?.[`task${task.id}`] ?? 'Pendiente' }}</p>
                        </li>
                    </ul>
                </article>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
