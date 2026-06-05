<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    panelTitle: { type: String, default: 'Gestión de Alumnos' },
    students: { type: Array, default: () => [] },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const createForm = useForm({
    name: '',
    email: '',
    role: 'alumno',
    password: '',
});

const updateForm = useForm({
    user_id: props.students[0]?.id ?? '',
    name: props.students[0]?.name ?? '',
    email: props.students[0]?.email ?? '',
    role: 'alumno',
});

const deleteForm = useForm({ user_id: '' });

const syncUpdateForm = () => {
    const selected = props.students.find((student) => Number(student.id) === Number(updateForm.user_id));
    if (!selected) return;

    updateForm.name = selected.name;
    updateForm.email = selected.email;
    updateForm.role = 'alumno';
};

const submitCreate = () => {
    createForm.post(route('panel.admin.users.create'), {
        preserveScroll: true,
        onSuccess: () => createForm.reset('name', 'email', 'password'),
    });
};

const submitUpdate = () => {
    updateForm.patch(route('panel.admin.users.update'), { preserveScroll: true });
};

const submitDelete = () => {
    if (!confirm('¿Seguro que quieres eliminar este alumno?')) return;
    deleteForm.delete(route('panel.admin.users.delete'), { preserveScroll: true });
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
                    <h3 class="text-xl font-bold text-white">Crear alumno</h3>
                    <form class="mt-4 space-y-3" @submit.prevent="submitCreate">
                        <input v-model="createForm.name" type="text" placeholder="Nombre" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input v-model="createForm.email" type="email" placeholder="Email" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input v-model="createForm.password" type="password" placeholder="Contraseña inicial" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <button type="submit" class="w-full rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Crear alumno</button>
                    </form>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Editar alumno</h3>
                    <form class="mt-4 space-y-3" @submit.prevent="submitUpdate">
                        <select v-model="updateForm.user_id" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" @change="syncUpdateForm">
                            <option disabled value="">Selecciona alumno</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                        </select>
                        <input v-model="updateForm.name" type="text" placeholder="Nombre" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input v-model="updateForm.email" type="email" placeholder="Email" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <button type="submit" class="w-full rounded-lg bg-cyan-600 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-500">Guardar cambios</button>
                    </form>
                </article>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">Listado de alumnos</h3>
                <div class="mt-4 overflow-auto rounded-lg border border-slate-700">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-800 text-slate-300">
                            <tr>
                                <th class="px-3 py-2">Nombre</th>
                                <th class="px-3 py-2">Email</th>
                                <th class="px-3 py-2">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-900/60 text-slate-200">
                            <tr v-for="student in students" :key="`row-${student.id}`">
                                <td class="px-3 py-2">{{ student.name }}</td>
                                <td class="px-3 py-2">{{ student.email }}</td>
                                <td class="px-3 py-2">
                                    <button
                                        type="button"
                                        class="rounded-md bg-rose-600 px-2 py-1 text-xs font-semibold text-white hover:bg-rose-500"
                                        @click="deleteForm.user_id = student.id; submitDelete();"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
