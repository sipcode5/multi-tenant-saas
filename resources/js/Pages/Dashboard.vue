<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const org = computed(() => page.props.currentOrganization);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <!-- Welcome Card -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Welcome back, {{ page.props.auth.user.name }}!
                        </h3>
                        <p v-if="org" class="mt-1 text-gray-600">
                            You're viewing <strong>{{ org.name }}</strong>.
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Link
                        v-if="org"
                        :href="route('organizations.members.index', org.slug)"
                        class="block bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Members</p>
                                <p class="text-sm text-gray-500">Manage team members</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        :href="route('billing.index')"
                        class="block bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Billing</p>
                                <p class="text-sm text-gray-500">Manage subscription</p>
                            </div>
                        </div>
                    </Link>

                    <Link
                        :href="route('activity-log.index')"
                        class="block bg-white shadow-sm sm:rounded-lg p-6 hover:shadow-md transition"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">Activity Log</p>
                                <p class="text-sm text-gray-500">View recent events</p>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Org Settings Link -->
                <div v-if="org" class="text-right">
                    <Link
                        :href="route('organizations.settings', org.slug)"
                        class="text-sm text-indigo-600 hover:underline"
                    >
                        Organisation Settings →
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

