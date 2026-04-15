<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    plans: Array,
    currentSubscription: Object,
    organization: Object,
});

const page = usePage();
const confirmingSubscribe = ref(null); // plan to subscribe to
const confirmingCancel = ref(false);
const subscribeForm = useForm({ plan_id: null });
const cancelForm = useForm({});

const isSubscribed = computed(() => !!props.currentSubscription && props.currentSubscription.stripe_status === 'active');
const currentPlanId = computed(() => props.currentSubscription?.plan_id ?? null);

function subscribe() {
    subscribeForm.plan_id = confirmingSubscribe.value.id;
    subscribeForm.post(route('billing.subscribe'), {
        onSuccess: () => { confirmingSubscribe.value = null; },
    });
}

function cancel() {
    cancelForm.post(route('billing.cancel'), {
        onSuccess: () => { confirmingCancel.value = false; },
    });
}
</script>

<template>
    <Head title="Billing" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Billing</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">

                <!-- Current Plan Banner -->
                <div v-if="isSubscribed" class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-800">
                    <p class="font-medium">Active Subscription</p>
                    <p class="text-sm mt-1">
                        Currently on the
                        <strong>{{ plans.find(p => p.id === currentPlanId)?.name ?? 'Unknown' }}</strong> plan.
                        <span v-if="currentSubscription?.ends_at" class="text-green-600">
                            Cancels on {{ new Date(currentSubscription.ends_at).toLocaleDateString() }}.
                        </span>
                    </p>
                </div>
                <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-800">
                    <p class="font-medium">No active subscription</p>
                    <p class="text-sm mt-1">Select a plan below to get started.</p>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        :class="[
                            'relative bg-white shadow-sm rounded-lg overflow-hidden border-2 transition',
                            plan.is_popular ? 'border-indigo-500' : 'border-gray-200',
                        ]"
                    >
                        <div v-if="plan.is_popular" class="bg-indigo-500 text-white text-xs text-center font-semibold py-1">
                            Most Popular
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">{{ plan.name }}</h3>
                            <p class="mt-2 text-3xl font-bold text-gray-900">
                                ${{ (plan.price_monthly / 100).toFixed(0) }}
                                <span class="text-base font-normal text-gray-500">/mo</span>
                            </p>

                            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                                <li
                                    v-for="(value, feature) in (plan.features ?? {})"
                                    :key="feature"
                                    class="flex items-center gap-2"
                                >
                                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ value === true ? feature : `${feature}: ${value}` }}</span>
                                </li>
                            </ul>

                            <div class="mt-6">
                                <PrimaryButton
                                    v-if="!isSubscribed || currentPlanId !== plan.id"
                                    class="w-full justify-center"
                                    @click="confirmingSubscribe = plan"
                                >
                                    {{ isSubscribed ? 'Switch to this plan' : 'Subscribe' }}
                                </PrimaryButton>
                                <span
                                    v-else
                                    class="block text-center text-sm font-medium text-indigo-600 py-2"
                                >
                                    Current Plan
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancel Button -->
                <div v-if="isSubscribed && !currentSubscription?.ends_at" class="text-right">
                    <button
                        class="text-sm text-red-600 hover:underline"
                        @click="confirmingCancel = true"
                    >
                        Cancel subscription
                    </button>
                </div>
            </div>
        </div>

        <!-- Subscribe Modal -->
        <Modal :show="!!confirmingSubscribe" @close="confirmingSubscribe = null">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Subscribe to {{ confirmingSubscribe?.name }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    You'll be billed ${{ confirmingSubscribe ? (confirmingSubscribe.price_monthly / 100).toFixed(0) : 0 }}/month.
                    In a production environment this would use Stripe Checkout.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingSubscribe = null">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="subscribeForm.processing" @click="subscribe">
                        Confirm Subscription
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Cancel Modal -->
        <Modal :show="confirmingCancel" @close="confirmingCancel = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Cancel Subscription</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Your subscription will remain active until the end of the current billing period.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingCancel = false">Keep Subscription</SecondaryButton>
                    <DangerButton :disabled="cancelForm.processing" @click="cancel">
                        Cancel Subscription
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
