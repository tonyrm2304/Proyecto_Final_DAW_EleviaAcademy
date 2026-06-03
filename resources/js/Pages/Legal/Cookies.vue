<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Cookies" />

    <PublicLayout>
        <section class="mx-auto w-full max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
            <h1 class="text-4xl font-extrabold text-white">Política de Cookies</h1>
            <div class="mt-6 space-y-4 text-slate-300">
                <p>Elevia Academy utiliza cookies técnicas para mantener tu sesión, mejorar el rendimiento y facilitar la navegación.</p>
                <p>Podemos usar cookies analíticas para medir uso de la plataforma y optimizar contenidos, siempre con fines de mejora del servicio.</p>
                <p>Puedes gestionar o bloquear cookies desde la configuración de tu navegador.</p>
            </div>
        </section>
    </PublicLayout>
</template>
