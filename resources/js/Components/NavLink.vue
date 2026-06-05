<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
    ? 'inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold leading-5 text-cyan-200 bg-slate-900/75 ring-1 ring-cyan-400/50 shadow-sm transition duration-150 ease-in-out'
    : 'inline-flex items-center rounded-md px-3 py-2 text-sm font-semibold leading-5 text-slate-100/95 hover:text-cyan-200 hover:bg-slate-900/55 ring-1 ring-transparent hover:ring-cyan-400/35 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
