<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-6 text-center">Choose your plan</h1>
        <div class="grid md:grid-cols-2 gap-6">
            <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-2xl border border-midnight/5 p-6 space-y-4">
                <h2 class="text-2xl font-black text-midnight">{{ plan.name }}</h2>
                <p class="text-midnight/60 text-sm">{{ plan.description }}</p>
                <p class="text-3xl font-black text-coral">{{ plan.price }} <span class="text-sm text-midnight/50">{{ plan.currency }}/{{ plan.interval }}</span></p>
                <ul class="space-y-1 text-sm text-midnight/70">
                    <li v-for="f in plan.features" :key="f">• {{ f }}</li>
                </ul>
                <button v-if="plan.price > 0" @click="checkout(plan)" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">
                    Upgrade
                </button>
                <div v-else class="w-full py-3 text-center text-sm text-midnight/50">Current plan</div>
            </div>
        </div>
        <p v-if="message" class="mt-6 text-center text-sm text-teal">{{ message }}</p>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const plans = ref<any[]>([]);
const message = ref('');

onMounted(async () => {
    const { data } = await api.get('/api/v1/plans');
    plans.value = data.data;
});

async function checkout(plan: any) {
    const phone = prompt('Enter M-Pesa phone number');
    if (!phone) return;
    const { data } = await api.post('/api/v1/payments/checkout', {
        plan_id: plan.id,
        provider: 'mpesa',
        phone,
    });
    message.value = data.message;
}
</script>
