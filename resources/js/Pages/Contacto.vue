<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    accept_policy: false,
});

const successMessage = computed(() => page.props.flash?.success ?? null);

const submit = () => {
    form.post(route('contact.submit'), {
        preserveScroll: true,
        onSuccess: () => form.reset('subject', 'message'),
    });
};
</script>

<template>
    <Head title="Contacto" />

    <PublicLayout active="contacto">
        <section class="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="mb-12 text-center">
                <h1 class="text-4xl font-extrabold tracking-tight text-white md:text-5xl">
                    Contacta con Nosotros
                </h1>
                <p class="mt-3 text-slate-300">
                    ¿Preguntas? Nuestro equipo responde rápido para ayudarte a elegir tu próximo curso.
                </p>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6 shadow-xl shadow-cyan-900/20">
                    <h2 class="mb-6 text-2xl font-bold text-white">Envíanos un mensaje</h2>

                    <div
                        v-if="successMessage"
                        class="mb-4 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300"
                    >
                        {{ successMessage }}
                    </div>

                    <form class="space-y-4" @submit.prevent="submit">
                        <div>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Tu nombre"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-slate-100 placeholder-slate-400 outline-none transition focus:border-cyan-400"
                            >
                            <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="Tu email"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-slate-100 placeholder-slate-400 outline-none transition focus:border-cyan-400"
                            >
                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400">{{ form.errors.email }}</p>
                        </div>

                        <div>
                            <input
                                v-model="form.subject"
                                type="text"
                                placeholder="Asunto"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-slate-100 placeholder-slate-400 outline-none transition focus:border-cyan-400"
                            >
                            <p v-if="form.errors.subject" class="mt-1 text-xs text-rose-400">{{ form.errors.subject }}</p>
                        </div>

                        <div>
                            <textarea
                                v-model="form.message"
                                rows="5"
                                placeholder="Tu mensaje"
                                class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-slate-100 placeholder-slate-400 outline-none transition focus:border-cyan-400"
                            />
                            <p v-if="form.errors.message" class="mt-1 text-xs text-rose-400">{{ form.errors.message }}</p>
                        </div>

                        <label class="inline-flex items-start gap-3 text-sm text-slate-300">
                            <input
                                v-model="form.accept_policy"
                                type="checkbox"
                                class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-800 text-cyan-500"
                            >
                            <span>
                                Acepto la
                                <a :href="route('legal.privacy')" class="text-cyan-300 hover:text-cyan-200">
                                    Política de Privacidad
                                </a>
                            </span>
                        </label>
                        <p v-if="form.errors.accept_policy" class="text-xs text-rose-400">{{ form.errors.accept_policy }}</p>

                        <button
                            type="submit"
                            class="w-full rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-3 font-semibold text-white shadow-lg shadow-cyan-700/20 transition hover:brightness-110 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Enviando...' : 'Enviar Mensaje' }}
                        </button>
                    </form>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                        <h3 class="text-lg font-bold text-cyan-300">Email</h3>
                        <p class="mt-2 text-slate-200">contacto@eleviaacademy.com</p>
                    </div>

                    <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                        <h3 class="text-lg font-bold text-cyan-300">Teléfono</h3>
                        <p class="mt-2 text-slate-200">+34 900 123 456</p>
                    </div>

                    <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5">
                        <h3 class="text-lg font-bold text-cyan-300">Horario</h3>
                        <p class="mt-2 text-slate-200">Lunes a Viernes: 09:00 - 18:00</p>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/70">
                        <iframe
                            title="Ubicación Elevia Academy"
                            class="h-72 w-full"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            src="https://www.google.com/maps?q=Madrid,+Espa%C3%B1a&output=embed"
                        />
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
