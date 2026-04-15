<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    activities: Object, // paginated
    filters: Object,
    users: Array,
});

const filters = ref({
    user_id: props.filters?.user_id ?? '',
    event: props.filters?.event ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

function applyFilters() {
    router.get(route('activity-log.index'), filters.value, { preserveState: true });
}

function resetFilters() {
    filters.value = { user_id: '', event: '', date_from: '', date_to: '' };
    applyFilters();
}

function eventBadgeClass(event) {
    const map = {
        created: 'bg-green-100 text-green-800',
        updated: 'bg-blue-100 text-blue-800',
        deleted: 'bg-red-100 text-red-800',
    };
    return map[event] ?? 'bg-gray-100 text-gray-800';
}
</script>

<template>
    <Head title="Activity Log" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Activity Log</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">

                <!-- Filters -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-5">
                        <div class="flex flex-wrap gap-4 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                                <select
                                    v-model="filters.user_id"
                                    class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All users</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event</label>
                                <select
                                    v-model="filters.event"
                                    class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All events</option>
                                    <option value="created">Created</option>
                                    <option value="updated">Updated</option>
                                    <option value="deleted">Deleted</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                                <input
                                    type="date"
                                    v-model="filters.date_from"
                                    class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                                <input
                                    type="date"
                                    v-model="filters.date_to"
                                    class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                            <div class="flex gap-2">
                                <button
                                    @click="applyFilters"
                                    class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Filter
                                </button>
                                <button
                                    @click="resetFilters"
                                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Log Table -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-gray-600">Time</th>
                                <th class="px-6 py-3 text-gray-600">User</th>
                                <th class="px-6 py-3 text-gray-600">Event</th>
                                <th class="px-6 py-3 text-gray-600">Subject</th>
                                <th class="px-6 py-3 text-gray-600">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-if="!activities.data?.length">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">No activity found.</td>
                            </tr>
                            <tr
                                v-for="activity in activities.data"
                                :key="activity.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-6 py-3 text-gray-500 whitespace-nowrap">
                                    {{ new Date(activity.created_at).toLocaleString() }}
                                </td>
                                <td class="px-6 py-3 font-medium text-gray-800">
                                    {{ activity.causer?.name ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        :class="['px-2 py-1 rounded-full text-xs font-medium', eventBadgeClass(activity.event)]"
                                    >
                                        {{ activity.event }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">
                                    {{ activity.subject_type?.split('\\').pop() ?? '—' }}
                                    <span class="text-gray-400">#{{ activity.subject_id }}</span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ activity.description }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="activities.links?.length > 3" class="flex items-center justify-between px-6 py-4 border-t">
                        <p class="text-sm text-gray-600">
                            Showing {{ activities.from }} – {{ activities.to }} of {{ activities.total }}
                        </p>
                        <div class="flex gap-1">
                            <component
                                v-for="link in activities.links"
                                :key="link.label"
                                :is="link.url ? 'a' : 'span'"
                                :href="link.url ?? undefined"
                                v-html="link.label"
                                :class="[
                                    'px-3 py-1 text-sm rounded border',
                                    link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50',
                                    !link.url ? 'opacity-50 cursor-default' : 'cursor-pointer',
                                ]"
                                @click.prevent="link.url && router.get(link.url)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
