<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    courses: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const activeCategory = ref('Todos');

const categoryTabs = computed(() => [
    { id: 'Todos', name: 'Todos' },
    ...props.categories.map((category) => ({ id: String(category.id), name: category.name })),
]);

const filteredCourses = computed(() => {
    if (activeCategory.value === 'Todos') {
        return props.courses;
    }

    return props.courses.filter(
        (course) => String(course.category?.id ?? '') === activeCategory.value,
    );
});

const setCategory = (id) => {
    activeCategory.value = id;
};

const orientador = ref({
    interes: '',
    nivel: '',
    tiempo: '',
    enfoque: '',
});

const orientadorReady = computed(() => {
    return orientador.value.interes && orientador.value.nivel && orientador.value.tiempo && orientador.value.enfoque;
});

const recommendedCourses = computed(() => {
    if (!orientadorReady.value) {
        return [];
    }

    const withScore = props.courses.map((course) => {
        let score = 0;

        // Q1 Interés principal
        if (String(course.category?.id ?? '') === orientador.value.interes) {
            score += 4;
        }

        // Q2 Nivel inicial
        switch (orientador.value.nivel) {
            case 'principiante':
                if (course.duration_hours <= 20) score += 3;
                break;
            case 'intermedio':
                if (course.duration_hours > 15 && course.duration_hours <= 30) score += 3;
                break;
            case 'avanzado':
                if (course.duration_hours >= 25) score += 3;
                break;
            default:
                break;
        }

        // Q3 Disponibilidad semanal
        if (orientador.value.tiempo === 'baja' && course.duration_hours <= 20) score += 2;
        if (orientador.value.tiempo === 'media' && course.duration_hours > 15 && course.duration_hours <= 30) score += 2;
        if (orientador.value.tiempo === 'alta' && course.duration_hours >= 25) score += 2;

        // Q4 Enfoque (técnico vs negocio)
        const businessCategoryName = 'Negocio Digital';
        const isBusiness = (course.category?.name ?? '') === businessCategoryName;

        if (orientador.value.enfoque === 'negocio' && isBusiness) score += 3;
        if (orientador.value.enfoque === 'tecnico' && !isBusiness) score += 3;

        return { ...course, score };
    });

    return withScore
        .sort((a, b) => b.score - a.score)
        .slice(0, 3);
});
</script>

<template>
    <Head title="Cursos" />

    <PublicLayout active="cursos">
        <section class="mx-auto w-full max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <header class="text-center">
                <h1 class="text-4xl font-extrabold text-white md:text-5xl">Catálogo de Cursos</h1>
                <p class="mt-3 text-slate-300">Selecciona el curso que quieres dominar</p>
            </header>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <button
                    v-for="tab in categoryTabs"
                    :key="tab.id"
                    type="button"
                    class="rounded-full px-5 py-2 text-sm font-semibold transition"
                    :class="activeCategory === tab.id
                        ? 'bg-blue-500 text-white'
                        : 'bg-slate-700 text-slate-200 hover:bg-slate-600'"
                    @click="setCategory(tab.id)"
                >
                    {{ tab.name }}
                </button>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="course in filteredCourses"
                    :key="course.id"
                    class="overflow-hidden rounded-2xl border border-cyan-500/20 bg-slate-900/70"
                >
                    <div class="h-24 bg-gradient-to-r from-blue-500 to-cyan-500" />
                    <div class="p-5">
                        <h2 class="text-2xl font-bold text-white">{{ course.title }}</h2>
                        <p class="mt-2 line-clamp-3 text-sm text-slate-300">{{ course.short_description }}</p>
                        <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
                            <span>{{ course.category?.name ?? 'Sin categoría' }}</span>
                            <span>{{ course.duration_hours }} horas</span>
                        </div>
                        <p class="mt-2 text-xs text-cyan-300">
                            Docente: {{ course.teacher?.name ?? 'Asignación pendiente' }}
                        </p>
                        <Link
                            :href="route('login')"
                            class="mt-5 block w-full rounded-lg bg-blue-500 px-4 py-2.5 text-center font-semibold text-white transition hover:bg-blue-400"
                        >
                            Inscríbete
                        </Link>
                    </div>
                </article>
            </div>

            <p v-if="filteredCourses.length === 0" class="mt-10 text-center text-slate-300">
                No hay cursos visibles para esta categoría en este momento.
            </p>
        </section>

        <section class="mx-auto w-full max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h2 class="text-3xl font-extrabold text-white">Orientador de Cursos (Test de Afinidades)</h2>
                <p class="mt-2 text-slate-300">Responde 4 preguntas y te recomendamos los 3 cursos más adecuados para tu perfil.</p>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-200">1) Área que más te interesa</label>
                        <select v-model="orientador.interes" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Selecciona categoría</option>
                            <option v-for="category in props.categories" :key="category.id" :value="String(category.id)">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-200">2) Nivel actual</label>
                        <select v-model="orientador.nivel" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Selecciona nivel</option>
                            <option value="principiante">Principiante</option>
                            <option value="intermedio">Intermedio</option>
                            <option value="avanzado">Avanzado</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-200">3) Disponibilidad de tiempo</label>
                        <select v-model="orientador.tiempo" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Selecciona disponibilidad</option>
                            <option value="baja">Baja (pocas horas semanales)</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta (ritmo intensivo)</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-200">4) Enfoque deseado</label>
                        <select v-model="orientador.enfoque" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Selecciona enfoque</option>
                            <option value="tecnico">Técnico (desarrollo, IA, automatización)</option>
                            <option value="negocio">Negocio digital (marketing, monetización, growth)</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-slate-700 bg-slate-800/70 p-4">
                    <h3 class="text-xl font-bold text-cyan-300">Top 3 recomendados</h3>
                    <ul class="mt-3 space-y-2">
                        <li
                            v-for="course in recommendedCourses"
                            :key="`rec-${course.id}`"
                            class="rounded-md border border-slate-700 bg-slate-900/80 px-3 py-2"
                        >
                            <p class="font-semibold text-white">{{ course.title }}</p>
                            <p class="text-xs text-slate-300">
                                {{ course.category?.name ?? 'General' }} · {{ course.duration_hours }}h · Afinidad {{ course.score }}
                            </p>
                        </li>
                        <li v-if="!orientadorReady" class="text-sm text-slate-400">Completa las 4 respuestas para ver recomendaciones.</li>
                    </ul>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
