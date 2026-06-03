<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    active: {
        type: String,
        default: 'inicio',
    },
    showFooter: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const logoError = ref(false);

const user = computed(() => page.props.auth?.user ?? null);

const navItems = [
    { key: 'inicio', label: 'Inicio', route: 'home' },
    { key: 'cursos', label: 'Cursos', route: 'courses.index' },
    { key: 'nosotros', label: 'Nosotros', route: 'nosotros' },
    { key: 'contacto', label: 'Contacto', route: 'contact.show' },
];
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <header class="sticky top-0 z-40 border-b border-cyan-500/20 bg-slate-950/95 backdrop-blur">
            <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link :href="route('home')" class="group inline-flex items-center gap-3">
                    <img
                        v-if="!logoError"
                        src="/Logo%20EA.jpg"
                        alt="Logo Elevia Academy"
                        class="h-9 w-9 rounded-lg object-cover ring-1 ring-cyan-400/50"
                        @error="logoError = true"
                    >
                    <span
                        v-else
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/20 text-xs font-bold text-cyan-300"
                    >
                        EA
                    </span>
                    <span class="text-xl font-bold tracking-tight">
                        <span class="text-cyan-400">Elevia</span>
                        <span class="text-slate-100"> Academy</span>
                    </span>
                </Link>

                <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="route(item.route)"
                        class="transition hover:text-cyan-300"
                        :class="props.active === item.key ? 'text-cyan-300' : 'text-slate-200'
                        "
                    >
                        {{ item.label }}
                    </Link>

                    <Link
                        :href="user ? route('dashboard') : route('login')"
                        class="rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 px-4 py-2 font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110"
                    >
                        {{ user ? 'Mi panel' : 'Acceder' }}
                    </Link>
                </nav>
            </div>
        </header>

        <main>
            <slot />
        </main>

        <footer v-if="showFooter" class="mt-20 border-t border-cyan-500/20 bg-slate-950">
            <div class="mx-auto grid w-full max-w-7xl gap-10 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <div class="mb-3 inline-flex items-center gap-3">
                        <img
                            v-if="!logoError"
                            src="/Logo%20EA.jpg"
                            alt="Logo Elevia Academy"
                            class="h-10 w-10 rounded-lg object-cover ring-1 ring-cyan-400/50"
                        >
                        <span class="text-2xl font-bold">
                            <span class="text-cyan-400">Elevia</span>
                            <span class="text-slate-100"> Academy</span>
                        </span>
                    </div>
                    <p class="max-w-xs text-slate-300">
                        Impulsa tu futuro digital con formación tecnológica orientada a resultados reales.
                    </p>
                </div>

                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Enlaces</h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><Link :href="route('home')" class="hover:text-cyan-300">Inicio</Link></li>
                        <li><Link :href="route('courses.index')" class="hover:text-cyan-300">Cursos</Link></li>
                        <li><Link :href="route('contact.show')" class="hover:text-cyan-300">Contacto</Link></li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Redes</h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><a href="https://twitter.com" target="_blank" rel="noopener" class="hover:text-cyan-300">Twitter</a></li>
                        <li><a href="https://linkedin.com" target="_blank" rel="noopener" class="hover:text-cyan-300">LinkedIn</a></li>
                        <li><a href="https://github.com" target="_blank" rel="noopener" class="hover:text-cyan-300">GitHub</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="mb-4 text-lg font-semibold text-white">Legal</h3>
                    <ul class="space-y-2 text-slate-300">
                        <li><Link :href="route('legal.privacy')" class="hover:text-cyan-300">Privacidad</Link></li>
                        <li><Link :href="route('legal.terms')" class="hover:text-cyan-300">Términos y Condiciones</Link></li>
                        <li><Link :href="route('legal.cookies')" class="hover:text-cyan-300">Cookies</Link></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-cyan-500/20 py-6 text-center text-slate-400">
                © 2026 Elevia Academy. Todos los derechos reservados.
            </div>
        </footer>
    </div>
</template>
