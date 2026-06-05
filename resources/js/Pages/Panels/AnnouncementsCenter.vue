<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    panelTitle: { type: String, default: 'Tablón de Anuncios' },
    announcements: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    isStudent: { type: Boolean, default: false },
    centerInfo: { type: Object, default: () => ({}) },
    calendarPdfUrl: { type: String, default: '' },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const form = useForm({
    course_id: props.courses[0]?.id ?? '',
    type: 'general',
    title: '',
    body: '',
});

const submitAnnouncement = () => {
    form.post(route('panel.announcements.add'), {
        preserveScroll: true,
        onSuccess: () => form.reset('title', 'body'),
    });
};

const deleteAnnouncement = (announcementId) => {
    router.delete(route('panel.announcements.delete', { announcementId }), { preserveScroll: true });
};

const examAnnouncements = computed(() => props.announcements.filter((a) => (a.type ?? 'general') === 'exam_date'));
const generalAnnouncements = computed(() => props.announcements.filter((a) => (a.type ?? 'general') !== 'exam_date'));
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

            <div class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Información del centro</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-300">
                        <li><span class="font-semibold text-slate-100">Teléfono:</span> {{ centerInfo.phone }}</li>
                        <li><span class="font-semibold text-slate-100">Correo:</span> {{ centerInfo.email }}</li>
                        <li><span class="font-semibold text-slate-100">Ubicación:</span> {{ centerInfo.address }}</li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Calendario escolar</h3>
                    <p class="mt-2 text-sm text-slate-300">Consulta el calendario académico oficial en PDF.</p>
                    <a :href="calendarPdfUrl" target="_blank" rel="noopener" class="mt-4 inline-flex rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">
                        Abrir calendario escolar (PDF)
                    </a>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Fechas de exámenes</h3>

                    <ul class="mt-4 space-y-2">
                        <li v-for="announcement in examAnnouncements" :key="announcement.id" class="rounded-md border border-slate-700 bg-slate-800/80 p-3">
                            <p class="font-semibold text-cyan-300">{{ announcement.title }}</p>
                            <p class="mt-1 text-sm text-slate-200">{{ announcement.body }}</p>
                            <p class="mt-1 text-xs text-slate-400">Publicado por: {{ announcement.author_name }}</p>
                            <button v-if="!isStudent" type="button" class="mt-2 text-xs text-rose-300 hover:text-rose-200" @click="deleteAnnouncement(announcement.id)">Eliminar</button>
                        </li>
                        <li v-if="examAnnouncements.length === 0" class="text-sm text-slate-400">No hay fechas de examen publicadas todavía.</li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Novedades del centro</h3>

                    <ul class="mt-4 space-y-2">
                        <li v-for="announcement in generalAnnouncements" :key="announcement.id" class="rounded-md border border-slate-700 bg-slate-800/80 p-3">
                            <p class="font-semibold text-cyan-300">{{ announcement.title }}</p>
                            <p class="mt-1 text-sm text-slate-200">{{ announcement.body }}</p>
                            <p class="mt-1 text-xs text-slate-400">Publicado por: {{ announcement.author_name }}</p>
                            <button v-if="!isStudent" type="button" class="mt-2 text-xs text-rose-300 hover:text-rose-200" @click="deleteAnnouncement(announcement.id)">Eliminar</button>
                        </li>
                        <li v-if="generalAnnouncements.length === 0" class="text-sm text-slate-400">No hay novedades por ahora.</li>
                    </ul>
                </article>
            </div>

            <div v-if="!isStudent" class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">Publicar anuncio</h3>
                <p class="mt-1 text-sm text-slate-300">Puedes publicar novedades generales o nuevas fechas de examen.</p>

                <form class="mt-4 grid gap-3 md:grid-cols-2" @submit.prevent="submitAnnouncement">
                    <select v-model="form.course_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <option disabled value="">Curso</option>
                        <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                    </select>
                    <select v-model="form.type" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <option value="general">Novedad del centro</option>
                        <option value="exam_date">Fecha de examen</option>
                    </select>
                    <input v-model="form.title" type="text" placeholder="Título" class="md:col-span-2 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                    <textarea v-model="form.body" rows="3" placeholder="Descripción" class="md:col-span-2 rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" />
                    <button type="submit" class="md:col-span-2 rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white hover:bg-cyan-500">Publicar</button>
                </form>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
