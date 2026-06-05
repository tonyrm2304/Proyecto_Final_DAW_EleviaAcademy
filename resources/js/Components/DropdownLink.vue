<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full rounded-md px-4 py-2 text-start text-sm font-medium leading-5 text-slate-100 transition duration-150 ease-in-out hover:bg-slate-800/80 hover:text-cyan-200 focus:bg-slate-800/80 focus:text-cyan-200 focus:outline-none"
    >
        <slot />
    </Link>
</template>
