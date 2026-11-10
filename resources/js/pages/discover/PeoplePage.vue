<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-6">Discover People</h1>

        <div class="flex flex-wrap gap-3 mb-6">
            <input v-model="filters.city" placeholder="City" class="px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
            <select v-model="filters.interest_id" class="px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none">
                <option value="">All interests</option>
                <option v-for="i in interests" :key="i.id" :value="i.id">{{ i.name }}</option>
            </select>
            <button @click="load" class="px-4 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Search</button>
        </div>

        <div v-if="loading" class="text-midnight/60">Loading...</div>
        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="person in people" :key="person.public_id" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="h-12 w-12 rounded-full bg-midnight/10 overflow-hidden flex items-center justify-center">
                        <img v-if="person.avatar_url" :src="person.avatar_url" class="h-full w-full object-cover" />
                        <span v-else class="font-bold text-midnight/40">{{ person.display_name?.charAt(0) }}</span>
                    </div>
                    <div>
                        <p class="font-bold text-midnight">{{ person.display_name }}</p>
                        <p class="text-xs text-midnight/50">{{ person.age }} · {{ person.city }}</p>
                    </div>
                </div>
                <p class="text-sm text-midnight/60 line-clamp-2">{{ person.bio }}</p>
                <div class="mt-3 flex flex-wrap gap-1">
                    <span v-for="i in person.interests" :key="i.id" class="px-2 py-0.5 rounded-full bg-teal/10 text-teal text-xs">{{ i.name }}</span>
                </div>
                <router-link :to="`/people/${person.public_id}`" class="mt-4 inline-block text-coral text-sm font-medium">View profile</router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import api from '@/services/api';

const people = ref<any[]>([]);
const interests = ref<any[]>([]);
const loading = ref(false);
const filters = reactive({ city: '', interest_id: '' });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/people', { params: filters });
        people.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    const { data } = await api.get('/api/v1/interests');
    interests.value = data.interests;
    await load();
});
</script>
