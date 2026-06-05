<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { getCourseImage } from '@/utils/courseImages';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    course: { type: Object, required: true },
    students: { type: Array, default: () => [] },
    materials: { type: Array, default: () => [] },
    tasks: { type: Array, default: () => [] },
    exam: { type: Object, default: () => ({}) },
    panelTitle: { type: String, default: 'Panel Profesor' },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success ?? null);

const enrollmentForm = useForm({
    student_id: props.students[0]?.id ?? '',
    course_id: props.course.id,
    action: 'add',
});

const materialUrlForm = useForm({
    course_id: props.course.id,
    title: '',
    url: '',
});

const materialPdfForm = useForm({
    course_id: props.course.id,
    title: '',
    pdf: null,
});

const examForm = useForm({
    course_id: props.course.id,
    title: props.exam?.title ?? 'Evaluación final',
    description: props.exam?.description ?? '',
    is_active: Boolean(props.exam?.is_active),
});

const examResultForm = useForm({
    student_id: props.students[0]?.id ?? '',
    course_id: props.course.id,
    passed: false,
    grade: null,
});

const gradeForm = useForm({
    student_id: props.students[0]?.id ?? '',
    course_id: props.course.id,
    task_number: 1,
    grade: null,
});

const taskEditor = reactive({
    tasks: props.tasks.map((task) => ({
        title: task.title,
        description: task.description,
        is_visible: task.is_visible,
    })),
});

while (taskEditor.tasks.length < 3) {
    taskEditor.tasks.push({
        title: `Tarea ${taskEditor.tasks.length + 1}`,
        description: '',
        is_visible: true,
    });
}

const taskDefinitionForm = useForm({
    course_id: props.course.id,
    tasks: taskEditor.tasks,
});

const submitEnrollment = () => {
    enrollmentForm.post(route('panel.enrollment.update'), { preserveScroll: true });
};

const submitMaterialUrl = () => {
    materialUrlForm.post(route('panel.materials.add-url'), {
        preserveScroll: true,
        onSuccess: () => materialUrlForm.reset('title', 'url'),
    });
};

const onPdfFileChange = (event) => {
    materialPdfForm.pdf = event.target.files?.[0] ?? null;
};

const submitMaterialPdf = () => {
    materialPdfForm.post(route('panel.materials.add-pdf'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => materialPdfForm.reset('title', 'pdf'),
    });
};

const deleteMaterial = (materialId) => {
    router.delete(route('panel.materials.delete', { courseId: props.course.id, materialId }), {
        preserveScroll: true,
    });
};

const submitTaskDefinitions = () => {
    taskDefinitionForm.tasks = taskEditor.tasks;
    taskDefinitionForm.post(route('panel.tasks.update'), { preserveScroll: true });
};

const submitTaskGrade = () => {
    gradeForm.post(route('panel.tasks.grade'), { preserveScroll: true });
};

const submitExamConfig = () => {
    examForm.post(route('panel.exam.update'), { preserveScroll: true });
};

const submitExamResult = () => {
    examResultForm.post(route('panel.exam.result'), { preserveScroll: true });
};

const studentGrade = (student, taskNumber) => student?.state?.grades?.[`task${taskNumber}`] ?? 'Pendiente';
const taskSubmission = (student, taskNumber) => student?.state?.task_submissions?.[`task${taskNumber}`] ?? null;

const courseImage = computed(() => getCourseImage(props.course));

const deleteConfirm = ref(false);

const deleteCourse = () => {
    if (!deleteConfirm.value) {
        alert('Por favor confirma la eliminación.');
        return;
    }
    if (confirm('⚠️ Esta acción eliminará el curso y toda su información. ¿Estás seguro?')) {
        router.delete(route('panel.admin.courses.delete'), {
            data: { course_id: props.course.id, confirm: true },
        });
    }
};
</script>

<template>
    <Head :title="`Gestión Curso · ${course.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-white">{{ panelTitle }} · Gestión de curso</h2>
                <Link :href="route('panel.profesor')" class="rounded-lg border border-cyan-500/40 px-3 py-2 text-sm font-semibold text-cyan-300 hover:bg-cyan-500/10">Volver al panel</Link>
            </div>
        </template>

        <section class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div v-if="successMessage" class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ successMessage }}</div>

            <img :src="courseImage" :alt="course.title" class="h-64 w-full rounded-2xl object-cover" @error="$event.target.src = '/imagenes/logo/Logo_EA.jpg'">

            <div class="mt-6 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">{{ course.title }}</h3>
                <p class="mt-2 text-slate-300">{{ course.long_description }}</p>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Matrículas del curso</h3>
                    <p class="mt-1 text-sm text-slate-300">Altas y bajas por curso específico (sin gestión global).</p>

                    <form class="mt-4 grid gap-3" @submit.prevent="submitEnrollment">
                        <select v-model="enrollmentForm.student_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option disabled value="">Alumno</option>
                            <option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option>
                        </select>

                        <select v-model="enrollmentForm.action" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                            <option value="add">Matricular en este curso</option>
                            <option value="remove">Dar de baja de este curso</option>
                        </select>

                        <button type="submit" class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2.5 font-semibold text-white">Aplicar</button>
                    </form>

                    <ul class="mt-4 space-y-2">
                        <li v-for="student in students" :key="student.id" class="rounded-md border border-slate-700 bg-slate-800/70 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-100">{{ student.name }}</p>
                                    <p class="text-xs text-slate-400">{{ student.email }}</p>
                                </div>
                                <span class="rounded-full px-2 py-1 text-xs" :class="student.is_enrolled ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-300'">
                                    {{ student.is_enrolled ? 'Matriculado' : 'No matriculado' }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Contenido didáctico</h3>

                    <form class="mt-4 grid gap-3" @submit.prevent="submitMaterialUrl">
                        <input v-model="materialUrlForm.title" type="text" placeholder="Título del recurso" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input v-model="materialUrlForm.url" type="url" placeholder="https://..." class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white hover:bg-cyan-500">Añadir enlace</button>
                    </form>

                    <form class="mt-4 grid gap-3" @submit.prevent="submitMaterialPdf">
                        <input v-model="materialPdfForm.title" type="text" placeholder="Título del PDF" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <input type="file" accept="application/pdf" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" @change="onPdfFileChange">
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2.5 font-semibold text-white hover:bg-blue-500">Subir PDF</button>

                        <p v-if="materialPdfForm.errors.pdf" class="text-xs text-rose-300">{{ materialPdfForm.errors.pdf }}</p>
                        <p v-if="materialPdfForm.errors.title" class="text-xs text-rose-300">{{ materialPdfForm.errors.title }}</p>
                        <p class="text-xs text-slate-400">
                            Tras subirlo, aparecerá abajo en "Contenido didáctico". Si no sale al instante, recarga la vista del curso.
                        </p>
                    </form>

                    <ul class="mt-4 space-y-2">
                        <li v-for="material in materials" :key="material.id" class="flex items-center justify-between rounded-md border border-slate-700 bg-slate-800/70 px-3 py-2">
                            <a :href="material.url" target="_blank" rel="noopener" class="text-sm text-slate-100 hover:text-cyan-300">{{ material.title }}</a>
                            <button type="button" class="text-xs text-rose-300 hover:text-rose-200" @click="deleteMaterial(material.id)">Eliminar</button>
                        </li>
                        <li v-if="materials.length === 0" class="text-sm text-slate-400">Todavía no hay contenido subido para este curso.</li>
                    </ul>
                </article>
            </div>

            <div class="mt-8 grid gap-6 xl:grid-cols-2">
                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Configurar 3 tareas obligatorias</h3>
                    <div class="mt-4 space-y-3">
                        <div v-for="(task, index) in taskEditor.tasks" :key="`task-editor-${index}`" class="rounded-lg border border-slate-700 bg-slate-800/70 p-3">
                            <input v-model="task.title" type="text" :placeholder="`Título tarea ${index + 1}`" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-slate-100">
                            <textarea v-model="task.description" rows="2" placeholder="Descripción" class="mt-2 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-slate-100" />
                            <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-300">
                                <input v-model="task.is_visible" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400">
                                Visible para alumnado
                            </label>
                        </div>
                    </div>

                    <button type="button" class="mt-4 w-full rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white hover:bg-cyan-500" @click="submitTaskDefinitions">Guardar definición de tareas</button>
                </article>

                <article class="rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                    <h3 class="text-xl font-bold text-white">Configurar evaluación final</h3>
                    <form class="mt-4 space-y-3" @submit.prevent="submitExamConfig">
                        <input v-model="examForm.title" type="text" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" placeholder="Título evaluación">
                        <textarea v-model="examForm.description" rows="3" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100" placeholder="Descripción" />
                        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                            <input v-model="examForm.is_active" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400">
                            Examen activo
                        </label>
                        <button type="submit" class="w-full rounded-lg bg-cyan-600 px-4 py-2.5 font-semibold text-white hover:bg-cyan-500">Guardar evaluación</button>
                    </form>

                    <div class="mt-5 rounded-lg border border-slate-700 bg-slate-800/60 p-3">
                        <h4 class="font-semibold text-slate-100">Calificar y aprobar examen final</h4>
                        <form class="mt-3 grid gap-2" @submit.prevent="submitExamResult">
                            <select v-model="examResultForm.student_id" class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-slate-100">
                                <option disabled value="">Alumno</option>
                                <option v-for="student in students" :key="`exam-${student.id}`" :value="student.id">{{ student.name }}</option>
                            </select>
                            <input v-model.number="examResultForm.grade" type="number" min="0" max="10" step="0.1" placeholder="Nota examen (0-10)" class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-slate-100">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                                <input v-model="examResultForm.passed" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-cyan-500 focus:ring-cyan-400">
                                Examen aprobado (+20% automático)
                            </label>
                            <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500">Guardar resultado final</button>
                        </form>
                    </div>
                </article>
            </div>

            <div class="mt-8 rounded-2xl border border-cyan-500/20 bg-slate-900/70 p-6">
                <h3 class="text-xl font-bold text-white">Seguimiento automático de progreso (50/30/20)</h3>
                <p class="mt-2 text-sm text-slate-300">El porcentaje se actualiza por acciones del alumno y no se edita manualmente.</p>

                <div class="mt-4 overflow-auto rounded-lg border border-slate-700">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-800 text-slate-300">
                            <tr>
                                <th class="px-3 py-2">Alumno</th>
                                <th class="px-3 py-2">Progreso</th>
                                <th class="px-3 py-2">Tarea 1</th>
                                <th class="px-3 py-2">Tarea 2</th>
                                <th class="px-3 py-2">Tarea 3</th>
                                <th class="px-3 py-2">Final</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-900/60 text-slate-200">
                            <tr v-for="student in students" :key="`row-${student.id}`">
                                <td class="px-3 py-2">{{ student.name }}</td>
                                <td class="px-3 py-2">{{ student.progress }}%</td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-slate-300">{{ studentGrade(student, 1) }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-slate-300">{{ studentGrade(student, 2) }}</span>
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-xs text-slate-300">{{ studentGrade(student, 3) }}</span>
                                </td>
                                <td class="px-3 py-2">{{ student?.state?.grades?.final ?? 'Pendiente' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <form class="mt-4 grid gap-3 md:grid-cols-4" @submit.prevent="submitTaskGrade">
                    <select v-model="gradeForm.student_id" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <option disabled value="">Alumno</option>
                        <option v-for="student in students" :key="`grade-${student.id}`" :value="student.id">{{ student.name }}</option>
                    </select>
                    <select v-model="gradeForm.task_number" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                        <option :value="1">Tarea 1</option>
                        <option :value="2">Tarea 2</option>
                        <option :value="3">Tarea 3</option>
                    </select>
                    <input v-model.number="gradeForm.grade" type="number" min="0" max="10" step="0.1" placeholder="Nota (0-10)" class="rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-slate-100">
                    <button type="submit" class="rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-cyan-500">Guardar nota tarea</button>
                </form>

                <div class="mt-8">
                    <h4 class="text-lg font-bold text-white">Entregas recibidas</h4>
                    <p class="mt-1 text-sm text-slate-300">Consulta las evidencias subidas por el alumnado para cada tarea obligatoria.</p>

                    <div class="mt-4 space-y-4">
                        <article
                            v-for="student in students.filter((item) => item.is_enrolled)"
                            :key="`submission-${student.id}`"
                            class="rounded-xl border border-slate-700 bg-slate-800/60 p-4"
                        >
                            <p class="font-semibold text-slate-100">{{ student.name }}</p>
                            <p class="text-xs text-slate-400">{{ student.email }}</p>

                            <div class="mt-3 grid gap-3 md:grid-cols-3">
                                <div
                                    v-for="taskNumber in [1, 2, 3]"
                                    :key="`st-${student.id}-task-${taskNumber}`"
                                    class="rounded-lg border border-slate-700 bg-slate-900/80 p-3"
                                >
                                    <p class="text-xs font-semibold text-cyan-300">Tarea {{ taskNumber }}</p>

                                    <template v-if="taskSubmission(student, taskNumber)">
                                        <a
                                            :href="taskSubmission(student, taskNumber).url"
                                            target="_blank"
                                            rel="noopener"
                                            class="mt-2 inline-block text-xs font-semibold text-emerald-300 hover:text-emerald-200"
                                        >
                                            Ver archivo: {{ taskSubmission(student, taskNumber).file_name }}
                                        </a>
                                    </template>

                                    <p v-else class="mt-2 text-xs text-slate-400">Sin entrega todavía.</p>
                                </div>
                            </div>
                        </article>

                        <p v-if="students.filter((item) => item.is_enrolled).length === 0" class="text-sm text-slate-400">
                            No hay alumnado matriculado en este curso.
                        </p>
                    </div>
                </div>

                <div class="mt-8">
                    <h4 class="text-lg font-bold text-rose-400">Zona de Riesgo</h4>
                    <p class="mt-2 text-sm text-slate-300">Acciones que no se pueden deshacer. Procede con cuidado.</p>
                    <form class="mt-4 rounded-lg border border-rose-500/30 bg-rose-900/10 p-4" @submit.prevent="deleteCourse">
                        <p class="text-sm text-slate-200">Al eliminar este curso se borrará toda la información asociada (matrículas, tareas, entregas, etc.)</p>
                        <div class="mt-3 flex gap-3">
                            <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-500">
                                🗑️ Eliminar Curso
                            </button>
                            <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                                <input v-model="deleteConfirm" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-rose-600 focus:ring-rose-500">
                                Confirmo que quiero eliminar este curso
                            </label>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
