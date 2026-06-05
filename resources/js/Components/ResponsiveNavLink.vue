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
    ? 'block w-full rounded-md px-3 py-2 text-start text-base font-semibold text-cyan-200 bg-slate-900/80 ring-1 ring-cyan-400/45 transition duration-150 ease-in-out'
    : 'block w-full rounded-md px-3 py-2 text-start text-base font-semibold text-slate-100/95 hover:text-cyan-200 hover:bg-slate-900/60 ring-1 ring-transparent hover:ring-cyan-400/30 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
