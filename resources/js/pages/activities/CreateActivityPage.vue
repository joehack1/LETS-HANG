<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-6">Create Activity</h1>
        <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">Title</label>
                <input v-model="form.title" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none"></textarea>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select v-model="form.category_id" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none">
                        <option value="">None</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Joining mode</label>
                    <select v-model="form.joining_mode" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none">
                        <option value="open">Open</option>
                        <option value="approval">Approval required</option>
                    </select>
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Starts at</label>
                    <input v-model="form.starts_at" type="datetime-local" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" required />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ends at</label>
                    <input v-model="form.ends_at" type="datetime-local" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input v-model="form.city" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Area</label>
                    <input v-model="form.area" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Venue</label>
                    <input v-model="form.venue_name" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Max participants</label>
                <input v-model.number="form.max_participants" type="number" min="2" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Instructions</label>
                <textarea v-model="form.instructions" rows="2" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none"></textarea>
            </div>
            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
            <button type="submit" :disabled="saving" class="w-full py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition disabled:opacity-50">
                {{ saving ? 'Creating...' : 'Create activity' }}
            </button>
        </form>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();
const categories = ref<any[]>([]);
const saving = ref(false);
const error = ref('');

const form = reactive({
    title: '',
    description: '',
    category_id: '',
    starts_at: '',
    ends_at: '',
    city: '',
    area: '',
    venue_name: '',
    max_participants: 20,
    joining_mode: 'open',
    instructions: '',
});

onMounted(async () => {
    const { data } = await api.get('/api/v1/activity-categories');
    categories.value = data.data;
});

async function submit() {
    saving.value = true;
    error.value = '';
    try {
        const payload: any = { ...form, country: 'KE' };
        if (payload.category_id === '') delete payload.category_id;
        const { data } = await api.post('/api/v1/activities', payload);
        router.push(`/activities/${data.activity.public_id}`);
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Failed to create activity.';
    } finally {
        saving.value = false;
    }
}
</script>
