<script setup>
import { computed, ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const role = computed(() => page.props.auth?.user?.role ?? null);
const logoError = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100">
        <nav
            class="border-b border-cyan-500/20 backdrop-blur"
            style="background-image: linear-gradient(rgba(2,6,23,.86), rgba(2,6,23,.86)), url('/imagenes/fondos/Navbar.jpg'); background-size: cover; background-position: center;"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex shrink-0 items-center">
                            <Link :href="route('home')" class="inline-flex items-center gap-3">
                                <img
                                    v-if="!logoError"
                                    src="/imagenes/logo/Logo_EA.jpg"
                                    alt="Logo Elevia Academy"
                                    class="h-9 w-9 rounded-lg object-cover ring-1 ring-cyan-400/50"
                                    @error="logoError = true"
                                >
                                <span
                                    v-else
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/20 text-xs font-bold text-cyan-300"
                                >EA</span>
                                <span class="font-bold tracking-tight text-white">
                                    <span class="text-cyan-400">Elevia</span> Academy
                                </span>
                            </Link>
                        </div>

                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <NavLink
                                v-if="role === 'alumno'"
                                :href="route('panel.alumno')"
                                :active="route().current('panel.alumno') || route().current('panel.alumno.course')"
                            >
                                Mi Panel Alumno
                            </NavLink>

                            <NavLink
                                v-if="role === 'alumno'"
                                :href="route('panel.messages')"
                                :active="route().current('panel.messages')"
                            >
                                Mensajes
                            </NavLink>

                            <NavLink
                                v-if="role === 'alumno'"
                                :href="route('panel.announcements')"
                                :active="route().current('panel.announcements')"
                            >
                                Tablón
                            </NavLink>

                            <NavLink
                                v-if="role === 'profesor' || role === 'admin'"
                                :href="route('panel.profesor')"
                                :active="route().current('panel.profesor') || route().current('panel.profesor.course')"
                            >
                                {{ role === 'admin' ? 'Panel Profesor Administrador' : 'Panel Profesor' }}
                            </NavLink>

                            <NavLink
                                v-if="role === 'profesor' || role === 'admin'"
                                :href="route('panel.messages')"
                                :active="route().current('panel.messages')"
                            >
                                Mensajes
                            </NavLink>

                            <NavLink
                                v-if="role === 'profesor' || role === 'admin'"
                                :href="route('panel.announcements')"
                                :active="route().current('panel.announcements')"
                            >
                                Tablón
                            </NavLink>

                            <NavLink
                                v-if="role === 'admin'"
                                :href="route('panel.admin.students')"
                                :active="route().current('panel.admin.students')"
                            >
                                Gestión Alumnos
                            </NavLink>

                            <NavLink
                                v-if="role === 'admin'"
                                :href="route('panel.admin.teachers')"
                                :active="route().current('panel.admin.teachers')"
                            >
                                Gestión Profesores
                            </NavLink>
                        </div>
                    </div>

                    <div class="hidden sm:ms-6 sm:flex sm:items-center">
                        <div class="relative ms-3">
                            <Dropdown
                                align="right"
                                width="48"
                                content-classes="py-1 bg-slate-900/95 border border-cyan-500/20"
                            >
                                <template #trigger>
                                    <span class="inline-flex rounded-md">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-cyan-400/25 bg-slate-900/80 px-3 py-2 text-sm font-semibold leading-4 text-slate-100 shadow-sm transition hover:border-cyan-400/50 hover:text-cyan-200 focus:outline-none"
                                        >
                                            {{ $page.props.auth.user.name }}
                                            <svg
                                                class="-me-0.5 ms-2 h-4 w-4"
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path
                                                    fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd"
                                                />
                                            </svg>
                                        </button>
                                    </span>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Perfil
                                    </DropdownLink>
                                    <DropdownLink
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                    >
                                        Cerrar Sesión
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>

                    <div class="-me-2 flex items-center sm:hidden">
                        <button
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                            class="inline-flex items-center justify-center rounded-md p-2 text-slate-300 transition hover:bg-slate-800 hover:text-cyan-300 focus:bg-slate-800 focus:text-cyan-300 focus:outline-none"
                        >
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path
                                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"
                                />
                                <path
                                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div
                :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                class="border-t border-cyan-500/20 bg-slate-950/90 sm:hidden"
            >
                <div class="space-y-1 pb-3 pt-2">
                    <ResponsiveNavLink
                        v-if="role === 'alumno'"
                        :href="route('panel.alumno')"
                        :active="route().current('panel.alumno') || route().current('panel.alumno.course')"
                    >
                        Mi Panel Alumno
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'alumno'"
                        :href="route('panel.messages')"
                        :active="route().current('panel.messages')"
                    >
                        Mensajes
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'alumno'"
                        :href="route('panel.announcements')"
                        :active="route().current('panel.announcements')"
                    >
                        Tablón
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'profesor' || role === 'admin'"
                        :href="route('panel.profesor')"
                        :active="route().current('panel.profesor') || route().current('panel.profesor.course')"
                    >
                        {{ role === 'admin' ? 'Panel Profesor Administrador' : 'Panel Profesor' }}
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'profesor' || role === 'admin'"
                        :href="route('panel.messages')"
                        :active="route().current('panel.messages')"
                    >
                        Mensajes
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'profesor' || role === 'admin'"
                        :href="route('panel.announcements')"
                        :active="route().current('panel.announcements')"
                    >
                        Tablón
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'admin'"
                        :href="route('panel.admin.students')"
                        :active="route().current('panel.admin.students')"
                    >
                        Gestión Alumnos
                    </ResponsiveNavLink>

                    <ResponsiveNavLink
                        v-if="role === 'admin'"
                        :href="route('panel.admin.teachers')"
                        :active="route().current('panel.admin.teachers')"
                    >
                        Gestión Profesores
                    </ResponsiveNavLink>
                </div>

                <div class="border-t border-cyan-500/20 pb-1 pt-4">
                    <div class="px-4">
                        <div class="text-base font-medium text-slate-100">
                            {{ $page.props.auth.user.name }}
                        </div>
                        <div class="text-sm font-medium text-slate-400">
                            {{ $page.props.auth.user.email }}
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">
                            Perfil
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('logout')"
                            method="post"
                            as="button"
                        >
                            Cerrar Sesión
                        </ResponsiveNavLink>
                    </div>
                </div>
            </div>
        </nav>

        <header class="border-b border-cyan-500/20 bg-slate-900/60" v-if="$slots.header">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <main>
            <slot />
        </main>
    </div>
</template>
