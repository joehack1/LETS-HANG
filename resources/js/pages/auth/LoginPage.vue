<template>
    <div class="min-h-screen flex items-center justify-center bg-warm-white px-4 py-12">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 border border-midnight/5">
            <h1 class="text-2xl font-black text-midnight mb-2">Welcome back</h1>
            <p class="text-sm text-midnight/60 mb-6">Log in to continue.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input v-model="form.email" type="email" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input v-model="form.password" type="password" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input v-model="form.remember" type="checkbox" />
                    <span>Remember me</span>
                </label>

                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                <button type="submit" :disabled="auth.loading" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition disabled:opacity-50">
                    {{ auth.loading ? 'Logging in...' : 'Log in' }}
                </button>
            </form>

            <div class="mt-4 flex justify-between text-sm">
                <router-link to="/forgot-password" class="text-coral font-medium">Forgot password?</router-link>
                <router-link to="/register" class="text-midnight/60 hover:text-coral">Create account</router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();
const error = ref('');

const form = reactive({ email: '', password: '', remember: false });

async function submit() {
    error.value = '';
    try {
        await auth.login({ email: form.email, password: form.password, remember: form.remember });
        router.push('/dashboard');
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Login failed.';
    }
}
</script>
