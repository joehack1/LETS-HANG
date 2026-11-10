<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div v-if="loading" class="text-midnight/60">Loading...</div>
        <div v-else-if="profile" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6 space-y-4">
            <div class="flex items-center gap-4">
                <div class="h-20 w-20 rounded-full bg-midnight/10 overflow-hidden flex items-center justify-center">
                    <img v-if="profile.avatar_url" :src="profile.avatar_url" class="h-full w-full object-cover" />
                    <span v-else class="text-2xl font-bold text-midnight/40">{{ profile.display_name?.charAt(0) }}</span>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-midnight">{{ profile.display_name }}</h1>
                    <p class="text-sm text-midnight/50">{{ profile.age }} · {{ profile.city }}, {{ profile.country }}</p>
                </div>
            </div>
            <p class="text-midnight/70">{{ profile.bio }}</p>
            <div class="flex flex-wrap gap-2">
                <span v-for="i in profile.interests" :key="i.id" class="px-3 py-1 rounded-full bg-teal/10 text-teal text-sm">{{ i.name }}</span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const profile = ref<any>(null);
const loading = ref(true);

onMounted(async () => {
    const { data } = await api.get(`/api/v1/people/${route.params.public_id}`);
    profile.value = data.profile;
    loading.value = false;
});
</script>
