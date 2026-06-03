<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';

const team = [
    { name: 'Alejandro Ruiz', role: 'Instructor Backend' },
    { name: 'Marta López', role: 'Mentora Frontend' },
    { name: 'Sofía Gálvez', role: 'Coach de Carrera' },
    { name: 'Daniel Soto', role: 'Tutor DevOps' },
];
</script>

<template>
    <Head title="Nosotros" />

    <PublicLayout active="nosotros">
        <section class="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-center text-4xl font-extrabold text-white md:text-5xl">Sobre Elevia Academy</h1>

            <div class="mt-10 grid gap-6 lg:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-7">
                    <h2 class="text-2xl font-bold text-white">Nuestra misión</h2>
                    <p class="mt-4 text-slate-300">
                        En Elevia Academy formamos talento digital con un enfoque práctico, accesible y conectado a las necesidades reales del sector tecnológico.
                    </p>
                    <p class="mt-3 text-slate-300">
                        Creemos en una educación aplicada que acelera la empleabilidad, mejora la toma de decisiones técnicas y potencia carreras sostenibles.
                    </p>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-7">
                    <h2 class="text-2xl font-bold text-cyan-300">En números</h2>
                    <ul class="mt-5 space-y-3 text-slate-200">
                        <li><span class="font-black text-cyan-300">50K+</span> estudiantes activos</li>
                        <li><span class="font-black text-cyan-300">200+</span> cursos disponibles</li>
                        <li><span class="font-black text-cyan-300">95%</span> satisfacción estudiantil</li>
                    </ul>
                </article>
            </div>

            <section class="mt-12">
                <h2 class="text-center text-3xl font-extrabold text-white">Nuestro equipo</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article
                        v-for="member in team"
                        :key="member.name"
                        class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-5 text-center"
                    >
                        <div class="mx-auto h-14 w-14 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500" />
                        <h3 class="mt-4 font-bold text-white">{{ member.name }}</h3>
                        <p class="text-sm text-slate-300">{{ member.role }}</p>
                    </article>
                </div>
            </section>

            <section class="mt-10 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-7">
                <h2 class="text-center text-3xl font-extrabold text-white">Nuestros valores</h2>
                <div class="mt-6 grid gap-4 text-center md:grid-cols-3">
                    <div>
                        <h3 class="font-bold text-cyan-300">Excelencia</h3>
                        <p class="text-slate-300">Contenido de alto nivel orientado a resultados.</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-cyan-300">Accesibilidad</h3>
                        <p class="text-slate-300">Educación tecnológica para todos los niveles.</p>
                    </div>
                    <div>
                        <h3 class="font-bold text-cyan-300">Innovación</h3>
                        <p class="text-slate-300">Aprendizaje continuo con herramientas actuales.</p>
                    </div>
                </div>
            </section>
        </section>
    </PublicLayout>
</template>