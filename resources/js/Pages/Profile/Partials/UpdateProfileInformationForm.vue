<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const avatarUrl = computed(() => {
    if (!user?.avatar || user.avatar === 'default-avatar.png') {
        return '/imagenes/logo/Logo_EA.jpg';
    }

    if (String(user.avatar).startsWith('http')) {
        return user.avatar;
    }

    return `/storage/${user.avatar}`;
});

const form = useForm({
    name: user.name,
    email: user.email,
    avatar: null,
});

const onAvatarChange = (event) => {
    form.avatar = event.target.files?.[0] ?? null;
};

const submitProfile = () => {
    form
        .transform((data) => ({
            ...data,
            _method: 'patch',
        }))
        .post(route('profile.update'), {
            forceFormData: true,
            preserveScroll: true,
        });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-slate-100">
                Información del perfil
            </h2>

            <p class="mt-1 text-sm text-slate-300">
                Actualiza tus datos básicos y tu foto de perfil.
            </p>
        </header>

        <form
            @submit.prevent="submitProfile"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="avatar" value="Foto de perfil" class="text-slate-200" />

                <div class="mt-2 flex items-center gap-4">
                    <img
                        :src="avatarUrl"
                        alt="Avatar"
                        class="h-16 w-16 rounded-full object-cover ring-2 ring-cyan-500/40"
                    >
                    <input
                        id="avatar"
                        type="file"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="block w-full rounded-md border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 file:mr-4 file:rounded-md file:border-0 file:bg-cyan-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-cyan-500"
                        @change="onAvatarChange"
                    >
                </div>

                <InputError class="mt-2" :message="form.errors.avatar" />
            </div>

            <div>
                <InputLabel for="name" value="Nombre" class="text-slate-200" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full border-slate-500 bg-slate-900 text-slate-100 placeholder:text-slate-400 shadow-inner shadow-slate-950/70"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-slate-200" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full border-slate-500 bg-slate-900 text-slate-100 placeholder:text-slate-400 shadow-inner shadow-slate-950/70"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-slate-300">
                    Tu dirección de correo no está verificada.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-cyan-300 underline hover:text-cyan-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                    >
                        Haz clic para reenviar el email de verificación.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-emerald-300"
                >
                    Se ha enviado un nuevo enlace de verificación a tu correo.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Guardar cambios</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-slate-300"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
