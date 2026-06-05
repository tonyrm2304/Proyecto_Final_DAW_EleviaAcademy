<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { courseImageByTitle } from '@/utils/courseImages';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    courses: {
        type: Array,
        default: () => [],
    },
});

const features = [
    {
        title: 'Instructores Expertos',
        description: 'Aprende de profesionales con experiencia real en proyectos de software y negocio digital.',
        icon: '🎓',
    },
    {
        title: 'Proyectos Reales',
        description: 'Construye portfolio desde el primer módulo con retos prácticos orientados al mercado.',
        icon: '💻',
    },
    {
        title: 'Certificados',
        description: 'Obtén certificados al finalizar itinerarios y demuestra competencias técnicas verificables.',
        icon: '🏆',
    },
    {
        title: 'Acceso Flexible',
        description: 'Contenido disponible 24/7 para estudiar según tu ritmo y disponibilidad.',
        icon: '📱',
    },
    {
        title: 'Comunidad',
        description: 'Comparte ideas con estudiantes y mentores en un entorno de aprendizaje colaborativo.',
        icon: '🤝',
    },
    {
        title: 'Soporte 24/7',
        description: 'Soporte académico para resolver dudas y mantener el avance de tu formación.',
        icon: '⭐',
    },
];
</script>

<template>
    <Head title="Inicio" />

    <PublicLayout active="inicio">
        <section
            class="relative overflow-hidden border-b border-cyan-500/20"
            style="background-image: linear-gradient(rgba(2,6,23,.76), rgba(2,6,23,.86)), url('/imagenes/fondos/Fondo_Formacion.jpg'); background-size: cover; background-position: center;"
        >
            <div class="mx-auto flex w-full max-w-7xl flex-col items-center px-4 py-16 text-center sm:px-6 lg:px-8">
                <h1 class="max-w-3xl text-4xl font-extrabold leading-tight text-white md:text-6xl drop-shadow">
                    Formación tecnológica para crecer con impacto real
                </h1>
                <p class="mt-5 max-w-2xl text-lg text-slate-300">
                    Elevia Academy conecta aprendizaje práctico, mentores expertos y proyectos aplicados para acelerar tu carrera digital.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <Link
                        :href="route('courses.index')"
                        class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-3 font-semibold text-white shadow-lg shadow-cyan-700/25 transition hover:brightness-110"
                    >
                        🚀 Explorar Cursos
                    </Link>
                    <Link
                        :href="route('login')"
                        class="rounded-lg border border-slate-600 bg-slate-800/70 px-6 py-3 font-semibold text-slate-100 transition hover:border-cyan-400 hover:text-cyan-200"
                    >
                        Acceder a mi Cuenta
                    </Link>
                </div>
            </div>
        </section>

        <section class="border-b border-cyan-500/20 bg-slate-900/70">
            <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-10 text-center sm:grid-cols-3 sm:px-6 lg:px-8">
                <div>
                    <p class="text-4xl font-black text-cyan-300">{{ courses.length }}+</p>
                    <p class="text-slate-300">Cursos Disponibles</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-cyan-300">10k+</p>
                    <p class="text-slate-300">Estudiantes Activos</p>
                </div>
                <div>
                    <p class="text-4xl font-black text-cyan-300">95%</p>
                    <p class="text-slate-300">Tasa de Satisfacción</p>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-4xl font-extrabold text-white">¿Por qué elegirnos?</h2>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6"
                >
                    <p class="text-3xl">{{ feature.icon }}</p>
                    <h3 class="mt-3 text-xl font-bold text-white">{{ feature.title }}</h3>
                    <p class="mt-2 text-slate-300">{{ feature.description }}</p>
                </article>
            </div>
        </section>

        <section class="mx-auto w-full max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
            <h2 class="mb-6 text-3xl font-extrabold text-white">Catálogo destacado</h2>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="course in courses"
                    :key="course.id"
                    class="flex h-full flex-col overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/70"
                >
                    <img
                        :src="courseImageByTitle(course.title)"
                        :alt="course.title"
                        class="h-44 w-full object-cover"
                        @error="$event.target.src = '/imagenes/logo/Logo_EA.jpg'"
                    >
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-xl font-bold text-white">{{ course.title }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm text-slate-300">{{ course.short_description }}</p>
                        <div class="mt-auto pt-4 flex items-center justify-between text-xs text-slate-400">
                            <span>{{ course.category?.name ?? 'General' }}</span>
                            <span>{{ course.duration_hours }} horas</span>
                        </div>
                    </div>
                </article>
            </div>

            <div class="mt-8 flex justify-center">
                <Link
                    :href="route('courses.index')"
                    class="rounded-lg border border-cyan-500/40 bg-slate-900/70 px-6 py-3 text-sm font-semibold text-cyan-300 transition hover:bg-cyan-500/10"
                >
                    Ver más cursos
                </Link>
            </div>
        </section>
    </PublicLayout>
</template>
