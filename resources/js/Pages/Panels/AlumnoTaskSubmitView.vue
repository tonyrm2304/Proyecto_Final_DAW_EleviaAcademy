<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    task: { type: Object, required: true },
    taskNumber: { type: Number, required: true },
    alreadySubmitted: { type: Boolean, default: false },
    submission: { type: Object, default: null },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const form = useForm({
    course_id: props.course.id,
    task_number: props.taskNumber,
    attachment: null,
    observations: '',
});

const onAttachmentChange = (event) => {
    form.attachment = event.target.files?.[0] ?? null;
};

const submit = () => {
    if (props.alreadySubmitted) {
        return;
    }

    const confirmed = window.confirm('¿Confirmas el envío? Una vez enviada la tarea, no podrás deshacer esta acción.');

    if (!confirmed) {
        return;
    }

    form.post(route('panel.alumno.submit-task-detailed'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Entrega · ${task.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-white">Entrega de tarea</h2>
                <Link :href="route('panel.alumno.course', { course: course.id })" class="rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">
                    Volver al curso
                </Link>
            </div>
        </template>

        <section class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ successMessage }}
            </div>

            <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <p class="text-sm text-cyan-300">{{ course.title }}</p>
                <h3 class="mt-1 text-2xl font-bold text-white">{{ task.title }}</h3>
                <p class="mt-2 text-sm text-slate-300">{{ task.description }}</p>

                <div class="mt-5 rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
                    Recordatorio: una vez enviada la tarea, no podrás deshacer esta acción.
                </div>

                <div v-if="alreadySubmitted" class="mt-5 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3">
                    <p class="text-sm font-semibold text-emerald-200">Esta tarea ya fue entregada.</p>
                    <p v-if="submission?.file_name" class="mt-1 text-xs text-slate-200">Archivo: {{ submission.file_name }}</p>
                    <p v-if="submission?.observations" class="mt-1 text-xs text-slate-300">Observaciones: {{ submission.observations }}</p>
                </div>

                <form v-else class="mt-6 space-y-4" @submit.prevent="submit">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Adjuntar tarea</label>
                        <input
                            type="file"
                            accept=".pdf,.doc,.docx,.txt,.zip,.rar"
                            required
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100"
                            @change="onAttachmentChange"
                        >
                        <p class="mt-1 text-xs text-slate-400">Formatos permitidos: PDF, DOC, DOCX, TXT, ZIP, RAR (máx. 20MB).</p>
                        <p v-if="form.errors.attachment" class="mt-1 text-xs text-rose-300">{{ form.errors.attachment }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-200">Observaciones</label>
                        <textarea
                            v-model="form.observations"
                            rows="4"
                            placeholder="Añade información extra sobre tu entrega (opcional)..."
                            class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100"
                        />
                        <p v-if="form.errors.observations" class="mt-1 text-xs text-rose-300">{{ form.errors.observations }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white hover:bg-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        {{ form.processing ? 'Enviando...' : 'Enviar tarea' }}
                    </button>
                </form>
            </article>
        </section>
    </AuthenticatedLayout>
</template>
