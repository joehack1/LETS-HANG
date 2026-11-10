<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-black text-midnight">Communities</h1>
            <router-link to="/communities/create" class="px-4 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Create community</router-link>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="c in communities" :key="c.slug" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-5">
                <h3 class="font-bold text-midnight text-lg">{{ c.name }}</h3>
                <p class="text-sm text-midnight/60 mt-1 line-clamp-2">{{ c.description }}</p>
                <div class="mt-3 text-xs text-midnight/50">{{ c.members_count }} members · {{ c.visibility }}</div>
                <router-link :to="`/communities/${c.slug}`" class="mt-3 inline-block text-coral text-sm font-medium">View</router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const communities = ref<any[]>([]);

onMounted(async () => {
    const { data } = await api.get('/api/v1/communities');
    communities.value = data.data;
});
</script>
