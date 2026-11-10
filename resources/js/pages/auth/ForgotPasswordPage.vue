<template>
    <div class="min-h-screen flex items-center justify-center bg-warm-white px-4 py-12">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 border border-midnight/5">
            <h1 class="text-2xl font-black text-midnight mb-2">Reset password</h1>
            <p class="text-sm text-midnight/60 mb-6">Enter your email to receive a reset link.</p>
            <form @submit.prevent="submit" class="space-y-4">
                <input v-model="email" type="email" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
                <p v-if="msg" class="text-sm text-teal">{{ msg }}</p>
                <button type="submit" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Send reset link</button>
            </form>
            <router-link to="/login" class="block mt-4 text-center text-sm text-coral font-medium">Back to login</router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import api from '@/services/api';

const email = ref('');
const msg = ref('');

async function submit() {
    const { data } = await api.post('/api/v1/forgot-password', { email: email.value });
    msg.value = data.message;
}
</script>
