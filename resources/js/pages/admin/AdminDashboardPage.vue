<template>
    <div>
        <h1 class="text-3xl font-black text-midnight mb-6">Admin Dashboard</h1>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="(v,k) in stats" :key="k" class="bg-white rounded-2xl border border-midnight/5 p-5">
                <p class="text-sm text-midnight/60">{{ k.replace(/_/g,' ') }}</p>
                <p class="text-2xl font-black text-midnight mt-1">{{ v }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const stats = ref<any>({});

onMounted(async () => {
    const { data } = await api.get('/api/v1/admin/dashboard');
    stats.value = data.stats;
});
</script>
