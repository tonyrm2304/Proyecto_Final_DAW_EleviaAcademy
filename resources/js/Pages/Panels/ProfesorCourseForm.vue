<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900">{{ course ? 'Editar Curso' : 'Crear Nuevo Curso' }}</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 md:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <form @submit.prevent="submit" class="p-6 space-y-6">
            <!-- Título -->
            <div>
              <label for="title" class="block text-sm font-medium text-gray-700">Título del Curso</label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2"
                placeholder="Ej: Introducción a React"
              />
              <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title[0] }}</p>
            </div>

            <!-- Descripción corta -->
            <div>
              <label for="short_description" class="block text-sm font-medium text-gray-700">Descripción Corta (Catálogo)</label>
              <textarea
                id="short_description"
                v-model="form.short_description"
                rows="2"
                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2"
                placeholder="Breve descripción que aparecerá en el catálogo"
              />
              <p v-if="form.errors.short_description" class="mt-1 text-sm text-red-600">{{ form.errors.short_description[0] }}</p>
            </div>

            <!-- Descripción larga -->
            <div>
              <label for="long_description" class="block text-sm font-medium text-gray-700">Descripción Completa</label>
              <textarea
                id="long_description"
                v-model="form.long_description"
                rows="4"
                class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2"
                placeholder="Descripción completa del curso, competencias, requisitos..."
              />
              <p v-if="form.errors.long_description" class="mt-1 text-sm text-red-600">{{ form.errors.long_description[0] }}</p>
            </div>

            <!-- Duración -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="duration_hours" class="block text-sm font-medium text-gray-700">Horas Lectivas</label>
                <input
                  id="duration_hours"
                  v-model.number="form.duration_hours"
                  type="number"
                  min="1"
                  max="200"
                  class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2"
                />
                <p v-if="form.errors.duration_hours" class="mt-1 text-sm text-red-600">{{ form.errors.duration_hours[0] }}</p>
              </div>

              <!-- Categoría -->
              <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría</label>
                <select
                  id="category_id"
                  v-model.number="form.category_id"
                  class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2"
                >
                  <option :value="null">Selecciona una categoría</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                    {{ cat.name }}
                  </option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id[0] }}</p>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-3 pt-4">
              <PrimaryButton :disabled="form.processing">
                {{ course ? 'Actualizar Curso' : 'Crear Curso' }}
              </PrimaryButton>
              <Link href="/panel/profesor" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
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
import PrimaryButton from '@/Components/PrimaryButton.vue'

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
})

const submit = () => {
  if (props.course) {
    form.patch(route('panel.profesor.course.update', props.course.id))
  } else {
    form.post(route('panel.profesor.course.store'))
  }
}
</script>

<style scoped>
input, textarea, select {
  @apply border;
}
</style>
