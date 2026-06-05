<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-900">Consultas de Contacto</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div v-if="submissions.length === 0" class="text-center py-8">
              <p class="text-gray-500">No hay consultas de contacto aún</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="submission in submissions"
                :key="submission.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start">
                  <div class="flex-1">
                    <p class="font-semibold text-gray-900">{{ submission.name }}</p>
                    <p class="text-sm text-gray-600">{{ submission.email }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                      <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                        {{ submission.subject }}
                      </span>
                    </p>
                    <p class="text-gray-700 mt-3 whitespace-pre-wrap">{{ submission.message }}</p>
                    <p class="text-xs text-gray-400 mt-3">
                      Recibido: {{ formatDate(submission.created_at) }}
                    </p>
                  </div>
                  <div class="ml-4 text-right">
                    <span
                      :class="[
                        'inline-block px-3 py-1 rounded-full text-sm font-medium',
                        submission.status === 'new'
                          ? 'bg-yellow-100 text-yellow-800'
                          : submission.status === 'read'
                            ? 'bg-blue-100 text-blue-800'
                            : 'bg-green-100 text-green-800',
                      ]"
                    >
                      {{ statusLabel(submission.status) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { defineProps } from 'vue'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  submissions: {
    type: Array,
    default: () => [],
  },
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const statusLabel = (status) => {
  const labels = {
    new: 'Nuevo',
    read: 'Leído',
    responded: 'Respondido',
  }
  return labels[status] || status
}
</script>
