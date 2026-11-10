<template>
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-6">Create Community</h1>
        <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input v-model="form.name" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Category</label>
                <input v-model="form.category" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Visibility</label>
                <select v-model="form.visibility" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                </select>
            </div>
            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
            <button type="submit" :disabled="saving" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition disabled:opacity-50">
                {{ saving ? 'Creating...' : 'Create community' }}
            </button>
        </form>
    </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();
const saving = ref(false);
const error = ref('');
const form = reactive({ name: '', description: '', category: '', visibility: 'public' });

async function submit() {
    saving.value = true;
    error.value = '';
    try {
        const { data } = await api.post('/api/v1/communities', form);
        router.push(`/communities/${data.community.slug}`);
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Failed to create community.';
    } finally {
        saving.value = false;
    }
}
</script>
