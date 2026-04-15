<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    organization: Object,
});

const form = useForm({
    name: props.organization.name,
});

const confirmingDelete = ref(false);
const deleteForm = useForm({});

function update() {
    form.patch(route('organizations.update', props.organization.slug));
}

function destroy() {
    deleteForm.delete(route('organizations.destroy', props.organization.slug), {
        onSuccess: () => { confirmingDelete.value = false; },
    });
}
</script>

<template>
    <Head title="Organisation Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Organisation Settings
            </h2>
        </template>

        <div class="py-12 space-y-6">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">

                <!-- Update Name -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Organisation Name</h3>
                        <form @submit.prevent="update" class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>
                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-red-600 mb-2">Danger Zone</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Permanently delete this organisation and all its data. This action cannot be undone.
                        </p>
                        <DangerButton @click="confirmingDelete = true">Delete Organisation</DangerButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="confirmingDelete" @close="confirmingDelete = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Organisation</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ organization.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = false">Cancel</SecondaryButton>
                    <DangerButton :disabled="deleteForm.processing" @click="destroy">
                        Delete Organisation
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
