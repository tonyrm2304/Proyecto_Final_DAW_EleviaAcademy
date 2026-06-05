<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-4xl font-black text-white">{{ course ? 'Editar Curso' : 'Crear Nuevo Curso' }}</h1>
          <p class="mt-2 text-cyan-300">{{ course ? 'Actualiza los detalles del curso' : 'Añade un nuevo curso al catálogo' }}</p>
        </div>
        <Link href="/panel/profesor" class="rounded-lg border border-cyan-500/40 px-4 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">← Volver</Link>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 md:px-8">
        <div class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 overflow-hidden shadow-lg">
          <form @submit.prevent="submit" class="p-8 space-y-6">
            <!-- Título -->
            <div>
              <label for="title" class="block text-sm font-semibold text-slate-200">Título del Curso</label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                class="mt-2 block w-full rounded-lg border border-slate-700 bg-slate-800 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-slate-100 px-4 py-2.5"
                placeholder="Ej: Introducción a React"
              />
              <p v-if="form.errors.title" class="mt-2 text-sm text-rose-400">{{ form.errors.title[0] }}</p>
            </div>

            <!-- Imagen del curso -->
            <div>
              <label for="image" class="block text-sm font-semibold text-slate-200">Imagen del Curso</label>
              <input
                id="image"
                type="file"
                accept="image/*"
                class="mt-2 block w-full rounded-lg border border-slate-700 bg-slate-800 shadow-sm text-slate-100 px-4 py-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cyan-600 file:text-white file:cursor-pointer hover:file:bg-cyan-500"
                @change="onImageChange"
              />
              <p v-if="form.errors.image" class="mt-2 text-sm text-rose-400">{{ form.errors.image[0] }}</p>
              <p class="mt-1 text-xs text-slate-400">JPG, PNG o WebP. Máximo 5MB</p>
            </div>

            <!-- Descripción corta -->
            <div>
              <label for="short_description" class="block text-sm font-semibold text-slate-200">Descripción Corta (Catálogo)</label>
              <textarea
                id="short_description"
                v-model="form.short_description"
                rows="2"
                class="mt-2 block w-full rounded-lg border border-slate-700 bg-slate-800 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-slate-100 px-4 py-2.5"
                placeholder="Breve descripción que aparecerá en el catálogo"
              />
              <p v-if="form.errors.short_description" class="mt-2 text-sm text-rose-400">{{ form.errors.short_description[0] }}</p>
            </div>

            <!-- Descripción larga -->
            <div>
              <label for="long_description" class="block text-sm font-semibold text-slate-200">Descripción Completa</label>
              <textarea
                id="long_description"
                v-model="form.long_description"
                rows="4"
                class="mt-2 block w-full rounded-lg border border-slate-700 bg-slate-800 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-slate-100 px-4 py-2.5"
                placeholder="Descripción completa del curso, competencias, requisitos..."
              />
              <p v-if="form.errors.long_description" class="mt-2 text-sm text-rose-400">{{ form.errors.long_description[0] }}</p>
            </div>

            <!-- Duración -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="duration_hours" class="block text-sm font-semibold text-slate-200">Horas Lectivas</label>
                <input
                  id="duration_hours"
                  v-model.number="form.duration_hours"
                  type="number"
                  min="1"
                  max="200"
                  class="mt-2 block w-full rounded-lg border border-slate-700 bg-slate-800 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-slate-100 px-4 py-2.5"
                />
                <p v-if="form.errors.duration_hours" class="mt-2 text-sm text-rose-400">{{ form.errors.duration_hours[0] }}</p>
              </div>

              <!-- Categoría -->
              <div>
                <label for="category_id" class="block text-sm font-semibold text-slate-200">Categoría</label>
                <select
                  id="category_id"
                  v-model.number="form.category_id"
                  class="mt-2 block w-full rounded-lg border border-slate-700 bg-slate-800 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-slate-100 px-4 py-2.5"
                >
                  <option :value="null">Selecciona una categoría</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
                <p v-if="form.errors.category_id" class="mt-2 text-sm text-rose-400">{{ form.errors.category_id[0] }}</p>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-6 border-t border-slate-700">
              <button type="submit" :disabled="form.processing" class="rounded-lg bg-gradient-to-r from-cyan-600 to-cyan-500 px-6 py-2.5 font-semibold text-white hover:from-cyan-500 hover:to-cyan-400 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ course ? 'Actualizar Curso' : 'Crear Curso' }}
              </button>
              <Link href="/panel/profesor" class="inline-flex items-center px-6 py-2.5 bg-slate-800 border border-slate-700 rounded-lg font-semibold text-sm text-slate-300 hover:bg-slate-700">
                Cancelar
              </Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  course: Object,
  categories: Array,
})

const form = useForm({
  title: props.course?.title || '',
  short_description: props.course?.short_description || '',
  long_description: props.course?.long_description || '',
  duration_hours: props.course?.duration_hours || 20,
  category_id: props.course?.category_id || null,
  image: null,
})

const onImageChange = (event) => {
  form.image = event.target.files[0] || null
}

const submit = () => {
  if (props.course) {
    form.patch(route('panel.profesor.course.update', props.course.id))
  } else {
    form.post(route('panel.profesor.course.store'))
  }
}
</script>
