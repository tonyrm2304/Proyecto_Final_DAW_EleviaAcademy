<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    panelTitle: { type: String, default: 'Mensajes' },
    contacts: { type: Array, default: () => [] },
    messages: { type: Array, default: () => [] },
    threads: { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
    isStudent: { type: Boolean, default: false },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);
const selectedThreadId = ref('');
const canDeleteThreads = computed(() => !props.isStudent);

const form = useForm({
    to_id: props.contacts[0]?.id ?? '',
    course_id: props.courses[0]?.id ?? '',
    thread_id: '',
    reply_to_id: null,
    subject: '',
    body: '',
    is_private: true,
});

const threadMessages = computed(() => {
    if (!selectedThreadId.value) return [];

    return props.messages
        .filter((message) => (message.thread_id ?? 'sin-hilo') === selectedThreadId.value)
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});

const openReply = (thread) => {
    selectedThreadId.value = thread.thread_id ?? 'sin-hilo';
    const me = page.props.auth?.user?.id;
    const recipient = Number(thread.from_id) === Number(me) ? thread.to_id : thread.from_id;

    form.to_id = recipient;
    form.course_id = thread.course_id ?? form.course_id;
    form.thread_id = thread.thread_id ?? '';
    form.reply_to_id = thread.id;
    form.subject = thread.subject?.startsWith('Re:') ? thread.subject : `Re: ${thread.subject}`;
    form.body = '';
};

const submitMessage = () => {
    form.post(route('panel.messages.send'), {
        preserveScroll: true,
        onSuccess: () => form.reset('body'),
    });
};

const deleteThread = (threadId) => {
    if (!canDeleteThreads.value) return;
    if (!confirm('¿Eliminar este hilo completo? Esta acción no se puede deshacer.')) return;

    router.delete(route('panel.messages.thread.delete'), {
        data: { thread_id: threadId },
        preserveScroll: true,
    });
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

            <div class="grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Conversaciones</h3>
                    <p class="mt-1 text-sm text-slate-300">
                        {{ isStudent ? 'Canal Alumno ↔ Profesorado' : 'Profesores/Superprofe pueden contactar con cualquier alumno' }}
                    </p>

                    <div class="mt-4 space-y-3">
                        <div v-for="thread in threads" :key="thread.id" class="rounded-lg border border-slate-700 bg-slate-800/80 p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-cyan-300">{{ thread.subject }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ new Date(thread.created_at).toLocaleString() }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" class="rounded-md border border-cyan-500/40 px-2 py-1 text-xs text-cyan-300 hover:bg-cyan-500/10" @click="openReply(thread)">
                                        Abrir
                                    </button>
                                    <button v-if="canDeleteThreads" type="button" class="rounded-md border border-rose-500/40 px-2 py-1 text-xs text-rose-300 hover:bg-rose-500/10" @click="deleteThread(thread.thread_id)">
                                        Eliminar hilo
                                    </button>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-slate-200">{{ thread.body }}</p>
                        </div>

                        <p v-if="threads.length === 0" class="text-sm text-slate-400">No tienes mensajes todavía.</p>
                    </div>

                    <div v-if="threadMessages.length" class="mt-5 rounded-lg border border-slate-700 bg-slate-950/60 p-3">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Hilo abierto</p>
                        <div class="mt-2 space-y-2">
                            <div v-for="msg in threadMessages" :key="msg.id" class="rounded-md border border-slate-800 bg-slate-900/80 px-3 py-2">
                                <p class="text-xs text-slate-400">{{ msg.from_name }} → {{ msg.to_name }}</p>
                                <p class="text-xs text-slate-400">{{ new Date(msg.created_at).toLocaleString() }}</p>
                                <p class="text-sm text-slate-100">{{ msg.body }}</p>
                            </div>
                        </div>
                    </div>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Nuevo mensaje</h3>

                    <form class="mt-4 space-y-3" @submit.prevent="submitMessage">
                        <select v-model="form.to_id" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Selecciona contacto</option>
                            <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                                {{ contact.name }} ({{ contact.role }})
                            </option>
                        </select>

                        <select v-model="form.course_id" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Curso relacionado (opcional)</option>
                            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.title }}</option>
                        </select>

                        <input v-model="form.subject" type="text" placeholder="Asunto" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <textarea v-model="form.body" rows="5" placeholder="Escribe tu mensaje" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" />

                        <div class="flex items-center gap-2">
                            <input id="is_private" v-model="form.is_private" type="checkbox" class="rounded border-slate-700 bg-slate-800">
                            <label for="is_private" class="text-sm text-slate-300">Mensaje privado (solo visible para emisor y receptor)</label>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white" :disabled="form.processing">
                            Enviar mensaje
                        </button>
                    </form>
                </article>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
