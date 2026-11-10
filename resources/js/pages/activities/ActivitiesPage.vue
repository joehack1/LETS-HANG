<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-black text-midnight">Activities</h1>
            <router-link to="/activities/create" class="px-4 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Create activity</router-link>
        </div>

        <div class="flex flex-wrap gap-3 mb-6">
            <input v-model="filters.search" placeholder="Search" class="px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
            <input v-model="filters.city" placeholder="City" class="px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
            <select v-model="filters.category_id" class="px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none">
                <option value="">All categories</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <button @click="load" class="px-4 py-2 rounded-xl bg-midnight text-white font-semibold hover:bg-midnight/90 transition">Filter</button>
        </div>

        <div v-if="loading" class="text-midnight/60">Loading...</div>
        <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <ActivityCard v-for="a in activities" :key="a.public_id" :activity="a" />
        </div>
        <p v-if="!loading && activities.length === 0" class="text-midnight/50 mt-6">No activities found.</p>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import api from '@/services/api';
import ActivityCard from '@/components/activities/ActivityCard.vue';

const activities = ref<any[]>([]);
const categories = ref<any[]>([]);
const loading = ref(false);
const filters = reactive({ search: '', city: '', category_id: '' });

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/v1/activities', { params: filters });
        activities.value = data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    const { data } = await api.get('/api/v1/activity-categories');
    categories.value = data.data;
    await load();
});
</script>
