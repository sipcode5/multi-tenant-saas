<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    organization: Object,
    members: Array,
    canManageMembers: Boolean,
});

const inviteForm = useForm({
    email: '',
    role: 'member',
});

const confirmingRemove = ref(null); // holds member to remove
const roleForm = useForm({ role: '' });

function invite() {
    inviteForm.post(route('organizations.members.invite', props.organization.slug), {
        onSuccess: () => inviteForm.reset(),
    });
}

function updateRole(member, role) {
    router.patch(route('organizations.members.updateRole', [props.organization.slug, member.id]), { role });
}

function removeMember() {
    router.delete(route('organizations.members.remove', [props.organization.slug, confirmingRemove.value.id]), {
        onSuccess: () => { confirmingRemove.value = null; },
    });
}
</script>

<template>
    <Head title="Members" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Members — {{ organization.name }}
            </h2>
        </template>

        <div class="py-12 space-y-6">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">

                <!-- Invite Form -->
                <div v-if="canManageMembers" class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Invite Member</h3>
                        <form @submit.prevent="invite" class="flex flex-wrap gap-4 items-end">
                            <div class="flex-1 min-w-48">
                                <InputLabel for="email" value="Email Address" />
                                <TextInput
                                    id="email"
                                    v-model="inviteForm.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    placeholder="colleague@example.com"
                                    required
                                />
                                <InputError class="mt-1" :message="inviteForm.errors.email" />
                            </div>
                            <div>
                                <InputLabel for="role" value="Role" />
                                <select
                                    id="role"
                                    v-model="inviteForm.role"
                                    class="mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="member">Member</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <PrimaryButton :disabled="inviteForm.processing">Send Invite</PrimaryButton>
                        </form>
                    </div>
                </div>

                <!-- Members List -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Members</h3>
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="border-b text-gray-600">
                                    <th class="py-2 pr-4">Name</th>
                                    <th class="py-2 pr-4">Email</th>
                                    <th class="py-2 pr-4">Role</th>
                                    <th v-if="canManageMembers" class="py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="member in members"
                                    :key="member.id"
                                    class="border-b last:border-0"
                                >
                                    <td class="py-3 pr-4 font-medium text-gray-800">{{ member.name }}</td>
                                    <td class="py-3 pr-4 text-gray-600">{{ member.email }}</td>
                                    <td class="py-3 pr-4">
                                        <select
                                            v-if="canManageMembers && member.pivot?.role !== 'owner'"
                                            :value="member.pivot?.role"
                                            @change="updateRole(member, $event.target.value)"
                                            class="text-sm border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500"
                                        >
                                            <option value="member">Member</option>
                                            <option value="admin">Admin</option>
                                        </select>
                                        <span v-else class="capitalize px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ member.pivot?.role }}
                                        </span>
                                    </td>
                                    <td v-if="canManageMembers" class="py-3">
                                        <DangerButton
                                            v-if="member.pivot?.role !== 'owner'"
                                            class="text-xs py-1 px-2"
                                            @click="confirmingRemove = member"
                                        >
                                            Remove
                                        </DangerButton>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remove Confirmation Modal -->
        <Modal :show="!!confirmingRemove" @close="confirmingRemove = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Remove Member</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Remove <strong>{{ confirmingRemove?.name }}</strong> from this organisation?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingRemove = null">Cancel</SecondaryButton>
                    <DangerButton @click="removeMember">Remove</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
