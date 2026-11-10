<template>
    <div class="min-h-screen flex items-center justify-center bg-warm-white px-4 py-12">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 border border-midnight/5">
            <h1 class="text-2xl font-black text-midnight mb-2">Choose new password</h1>
            <form @submit.prevent="submit" class="space-y-4">
                <input v-model="form.email" type="email" placeholder="Email" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
                <input v-model="form.password" type="password" placeholder="New password" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
                <input v-model="form.password_confirmation" type="password" placeholder="Confirm password" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
                <p v-if="msg" class="text-sm text-teal">{{ msg }}</p>
                <button type="submit" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Reset password</button>
            </form>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const router = useRouter();
const msg = ref('');
const form = reactive({ email: '', password: '', password_confirmation: '', token: '' });

async function submit() {
    form.token = String(route.query.token || '');
    const { data } = await api.post('/api/v1/reset-password', form);
    msg.value = data.message;
    setTimeout(() => router.push('/login'), 1500);
}
</script>
