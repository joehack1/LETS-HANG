<template>
    <div class="min-h-screen flex items-center justify-center bg-warm-white px-4 py-12">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 border border-midnight/5 space-y-6">
            <h1 class="text-2xl font-black text-midnight">Verify your account</h1>

            <div>
                <p class="text-sm text-midnight/60 mb-3">Email verification</p>
                <button @click="sendEmail" class="px-4 py-2 rounded-xl border border-midnight/20 hover:border-coral transition text-sm">Resend verification email</button>
                <p v-if="emailMsg" class="text-sm text-teal mt-2">{{ emailMsg }}</p>
            </div>

            <div>
                <p class="text-sm text-midnight/60 mb-3">Phone verification</p>
                <div class="flex gap-2">
                    <input v-model="otp" maxlength="6" class="flex-1 px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" placeholder="6-digit OTP" />
                    <button @click="sendOtp" class="px-4 py-2 rounded-xl border border-midnight/20 hover:border-coral transition text-sm">Send OTP</button>
                </div>
                <button @click="verifyOtp" class="mt-3 w-full py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Verify phone</button>
                <p v-if="phoneMsg" class="text-sm text-teal mt-2">{{ phoneMsg }}</p>
            </div>

            <router-link to="/dashboard" class="block text-center text-sm text-coral font-medium">Continue to dashboard</router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import api from '@/services/api';

const otp = ref('');
const emailMsg = ref('');
const phoneMsg = ref('');

async function sendEmail() {
    const { data } = await api.post('/api/v1/email/verification-notification');
    emailMsg.value = data.message;
}

async function sendOtp() {
    const { data } = await api.post('/api/v1/phone/send-otp');
    phoneMsg.value = data.message;
}

async function verifyOtp() {
    try {
        const { data } = await api.post('/api/v1/phone/verify-otp', { otp: otp.value });
        phoneMsg.value = data.message;
    } catch (e: any) {
        phoneMsg.value = e?.response?.data?.message || 'Verification failed.';
    }
}
</script>
