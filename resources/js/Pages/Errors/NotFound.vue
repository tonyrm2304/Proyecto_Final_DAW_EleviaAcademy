<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="404" />

    <PublicLayout>
        <section class="mx-auto flex min-h-[60vh] w-full max-w-4xl flex-col items-center justify-center px-4 py-16 text-center sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-cyan-300">Error 404</p>
            <h1 class="mt-4 text-5xl font-extrabold text-white md:text-6xl">Página no encontrada</h1>
            <p class="mt-4 max-w-xl text-slate-300">
                La ruta que intentaste abrir no existe o fue movida. Vuelve al inicio para continuar navegando.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <Link
                    :href="route('home')"
                    class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-3 font-semibold text-white shadow-lg shadow-cyan-700/25 transition hover:brightness-110"
                >
                    ← Volver al Inicio
                </Link>
                <Link
                    :href="route('courses.index')"
                    class="rounded-lg border border-cyan-500/40 px-6 py-3 font-semibold text-cyan-300 transition hover:bg-cyan-500/10"
                >
                    📚 Ver Cursos
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
