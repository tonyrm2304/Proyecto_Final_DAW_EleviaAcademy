<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

// Aquí recibimos los cursos que nos manda el controlador de Laravel
defineProps({
    courses: Array
});
</script>

<template>
    <Head title="Nuestros Cursos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catálogo de Cursos - Elevia Academy</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div v-for="course in courses" :key="course.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 flex flex-col justify-between p-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">{{ course.title }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ course.description }}</p>
                        </div>
                        
                        <div class="mt-4">
                            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded transition duration-150">
                                Ver Temario
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>