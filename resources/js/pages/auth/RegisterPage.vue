<template>
    <div class="min-h-screen flex items-center justify-center bg-warm-white px-4 py-12">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8 border border-midnight/5">
            <h1 class="text-2xl font-black text-midnight mb-2">Create your account</h1>
            <p class="text-sm text-midnight/60 mb-6">Join Let's Hang and find your people.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Full name</label>
                    <input v-model="form.name" type="text" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input v-model="form.email" type="email" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone number</label>
                    <input v-model="form.phone" type="tel" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Date of birth</label>
                        <input v-model="form.date_of_birth" type="date" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Country (ISO code)</label>
                        <input v-model="form.country" type="text" maxlength="2" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none uppercase" required />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input v-model="form.city" type="text" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input v-model="form.password" type="password" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Confirm password</label>
                    <input v-model="form.password_confirmation" type="password" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral focus:ring-2 focus:ring-coral/20 outline-none" required />
                </div>
                <label class="flex items-start gap-2 text-sm">
                    <input v-model="form.terms_accepted" type="checkbox" class="mt-1" required />
                    <span>I agree to the Terms of Service and Privacy Policy.</span>
                </label>

                <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

                <button type="submit" :disabled="auth.loading" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition disabled:opacity-50">
                    {{ auth.loading ? 'Creating account...' : 'Create account' }}
                </button>
            </form>

            <p class="mt-6 text-sm text-center text-midnight/60">
                Already have an account?
                <router-link to="/login" class="text-coral font-medium">Log in</router-link>
            </p>
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

const form = reactive({
    name: '',
    email: '',
    phone: '',
    date_of_birth: '',
    country: '',
    city: '',
    password: '',
    password_confirmation: '',
    terms_accepted: false,
});

async function submit() {
    error.value = '';
    try {
        await auth.register({ ...form, terms_accepted: form.terms_accepted ? true : false });
        router.push('/verify');
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Registration failed.';
    }
}
</script>
