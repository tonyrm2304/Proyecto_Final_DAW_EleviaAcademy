<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Términos y Condiciones" />

    <PublicLayout>
        <section class="mx-auto w-full max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold text-white">Términos y Condiciones</h1>
            <div class="mt-6 space-y-4 text-slate-300">
                <p>El uso de Elevia Academy implica la aceptación de estos términos. Los contenidos se ofrecen para formación académica y mejora profesional.</p>
                <p>El acceso de cada cuenta es personal e intransferible. El uso indebido puede suponer la suspensión temporal o permanente del acceso.</p>
                <p>Nos reservamos el derecho de actualizar contenidos y funcionalidades para mejorar la experiencia educativa.</p>
            </div>
        </section>
    </PublicLayout>
</template>
