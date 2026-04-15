<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const switching = ref(false);

function switchOrg(orgId) {
    if (switching.value) return;
    switching.value = true;
    router.post(route('organizations.switch'), { organization_id: orgId }, {
        onFinish: () => { switching.value = false; },
    });
}
</script>

<template>
    <div class="relative" v-if="page.props.userOrganizations?.length">
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-700">
                {{ page.props.currentOrganization?.name ?? 'No organisation' }}
            </span>
            <select
                class="text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                :value="page.props.currentOrganization?.id"
                @change="switchOrg($event.target.value)"
                :disabled="switching"
            >
                <option
                    v-for="org in page.props.userOrganizations"
                    :key="org.id"
                    :value="org.id"
                >
                    {{ org.name }}
                </option>
            </select>
        </div>
    </div>
</template>
