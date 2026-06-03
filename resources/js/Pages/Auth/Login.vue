<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Acceder" />

    <PublicLayout>
        <section class="mx-auto flex w-full max-w-7xl justify-center px-4 py-14 sm:px-6 lg:px-8">
            <div class="w-full max-w-md rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-8 shadow-xl shadow-cyan-900/20">
                <h1 class="text-center text-5xl font-extrabold text-white">Acceso</h1>
                <p class="mt-2 text-center text-slate-300">Inicia sesión en tu cuenta</p>

                <div v-if="status" class="mb-4 mt-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300">
                    {{ status }}
                </div>

                <form class="mt-8" @submit.prevent="submit">
                    <div>
                        <InputLabel for="email" value="Correo Electrónico" class="text-slate-200" />

                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full border-slate-700 bg-slate-800 text-slate-100"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="tu@email.com"
                        />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div class="mt-4">
                        <InputLabel for="password" value="Contraseña" class="text-slate-200" />

                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full border-slate-700 bg-slate-800 text-slate-100"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="mt-4 block">
                        <label class="flex items-center">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 text-sm text-slate-300">Recordarme</span>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-3">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="rounded-md text-sm text-cyan-300 underline hover:text-cyan-200 focus:outline-none"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>

                        <PrimaryButton
                            class="ms-auto bg-gradient-to-r from-blue-500 to-cyan-500 px-5 py-2.5"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Inicia Sesión
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </section>
    </PublicLayout>
</template>
