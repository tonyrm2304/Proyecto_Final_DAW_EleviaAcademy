<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
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
                    <h2 class="text-2xl font-bold text-cyan-300">¿Por qué elegirnos?</h2>
                    <ul class="mt-5 space-y-3 text-slate-200">
                        <li><span class="font-black text-cyan-300">✓</span> Cursos diseñados por profesionales en activo</li>
                        <li><span class="font-black text-cyan-300">✓</span> Enfoque 100% práctico y aplicado</li>
                        <li><span class="font-black text-cyan-300">✓</span> Mentorías personalizadas y soporte continuo</li>
                    </ul>
                </article>
            </div>

            <section class="mt-12 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-7">
                <h2 class="text-center text-3xl font-extrabold text-white">Nuestro equipo</h2>
                <p class="mt-6 text-center text-slate-300 max-w-2xl mx-auto">
                    Somos un equipo multidisciplinar de profesionales apasionados por el desarrollo web, la arquitectura de software y la innovación digital. Nacemos como una academia joven y entusiasta, con una mentalidad adaptada al ritmo del sector tecnológico actual. Cada miembro de Elevia Academy aporta su energía y visión fresca para asegurar que nuestros cursos estén siempre alineados con las herramientas, metodologías y prácticas más reales del mercado.Somos un equipo multidisciplinario de profesionales con más de 15 años de experiencia en desarrollo web, arquitectura de software e innovación digital. Cada miembro de Elevia Academy trae su expertise para asegurar que nuestros cursos están siempre alineados con las tecnologías y prácticas más actuales del mercado.
                </p>
                <p class="mt-4 text-center text-slate-400 text-sm">
                    Compartimos pasión por la educación tecnológica de vanguardia.
                </p>
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