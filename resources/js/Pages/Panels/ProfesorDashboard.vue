<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    courses: { type: Array, default: () => [] },
    activeStudents: { type: Array, default: () => [] },
    announcements: { type: Array, default: () => [] },
    isAdmin: { type: Boolean, default: false },
    panelTitle: { type: String, default: 'Panel Profesor' },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const announcementForm = useForm({
    course_id: props.courses[0]?.id ?? '',
    title: '',
    body: '',
});

const submitAnnouncement = () => {
    announcementForm.post(route('panel.announcements.add'), {
        preserveScroll: true,
        onSuccess: () => announcementForm.reset('title', 'body'),
    });
};

const deleteAnnouncement = (announcementId) => {
    router.delete(route('panel.announcements.delete', { announcementId }), { preserveScroll: true });
};

const toggleVisibility = (course) => {
    useForm({
        course_id: course.id,
        is_hidden: !course.is_hidden,
    }).post(route('panel.course.visibility'), { preserveScroll: true });
};
</script>

<template>
    <Head :title="panelTitle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">{{ panelTitle }}</h2>
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
                    <p class="mt-2 text-3xl font-black text-cyan-300">{{ activeStudents.length }}</p>
                </article>
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                    <p class="text-sm text-slate-400">Accesos rápidos</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <Link :href="route('panel.messages')" class="rounded-lg border border-cyan-500/40 px-3 py-1.5 text-xs font-semibold text-cyan-300 hover:bg-cyan-500/10">
                            Mensajes
                        </Link>
                        <Link v-if="isAdmin" :href="route('panel.admin.students')" class="rounded-lg border border-cyan-500/40 px-3 py-1.5 text-xs font-semibold text-cyan-300 hover:bg-cyan-500/10">
                            Gestión Alumnos
                        </Link>
                        <Link v-if="isAdmin" :href="route('panel.admin.teachers')" class="rounded-lg border border-cyan-500/40 px-3 py-1.5 text-xs font-semibold text-cyan-300 hover:bg-cyan-500/10">
                            Gestión Profesores
                        </Link>
                    </div>
                </article>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-xl font-bold text-white">Cursos gestionados (vistas independientes)</h3>
                    <div class="flex gap-2">
                        <Link v-if="isAdmin" :href="route('panel.profesor.course.new')" class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-3 py-2 text-sm font-semibold text-emerald-300 hover:bg-emerald-500/20">
                            + Nuevo Curso
                        </Link>
                        <a href="#tablon" class="rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">Ir al tablón</a>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <article v-for="course in courses" :key="course.id" class="rounded-xl border border-slate-700 bg-slate-800/80 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="font-semibold text-white">{{ course.title }}</h4>
                                <p class="text-xs text-slate-400">{{ course.is_hidden ? 'Oculto para alumnado' : 'Visible para alumnado' }}</p>
                            </div>
                            <button type="button" class="rounded-md border border-slate-600 px-2 py-1 text-xs text-slate-200 hover:bg-slate-700" @click="toggleVisibility(course)">
                                {{ course.is_hidden ? 'Publicar' : 'Ocultar' }}
                            </button>
                        </div>

                        <Link :href="route('panel.profesor.course', course.id)" class="mt-3 inline-flex rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">
                            Gestionar este curso
                        </Link>
                    </article>
                </div>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">Alumnos activos</h3>

                <div class="mt-4 grid gap-3 lg:grid-cols-2">
                    <div v-for="student in activeStudents" :key="student.id" class="rounded-xl border border-slate-700 bg-slate-800/80 p-4">
                        <div class="flex items-center gap-3">
                            <img :src="student.avatar && student.avatar !== 'default-avatar.png' ? `/storage/${student.avatar}` : '/imagenes/logo/Logo_EA.jpg'" :alt="student.name" class="h-10 w-10 rounded-full object-cover">
                            <div>
                                <p class="font-semibold text-slate-100">{{ student.name }}</p>
                                <p class="text-xs text-slate-400">{{ student.email }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span v-for="shared in student.shared_courses" :key="shared.id" class="rounded-full bg-cyan-500/20 px-2 py-1 text-xs text-cyan-300">{{ shared.title }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tablon" class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">Tablón de anuncios (compartido)</h3>

                <form class="mt-4 grid gap-3 md:grid-cols-2" @submit.prevent="submitAnnouncement">
                    <select v-model="announcementForm.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <option disabled value="">Curso</option>
                        <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                    </select>
                    <input v-model="announcementForm.title" type="text" placeholder="Título del anuncio" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                    <textarea v-model="announcementForm.body" rows="3" placeholder="Mensaje para el tablón" class="md:col-span-2 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" />
                    <button type="submit" class="md:col-span-2 rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white hover:bg-cyan-500">Publicar anuncio</button>
                </form>

                <ul class="mt-5 space-y-2">
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
                </ul>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
