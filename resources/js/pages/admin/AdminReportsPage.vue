<template>
    <div>
        <h1 class="text-3xl font-black text-midnight mb-6">Reports</h1>
        <div class="space-y-3">
            <div v-for="r in reports" :key="r.id" class="bg-white rounded-2xl border border-midnight/5 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-midnight">{{ r.reason }} · {{ r.reportable_type }}</p>
                        <p class="text-xs text-midnight/50">by {{ r.reporter }} · {{ r.status }}</p>
                        <p v-if="r.details" class="text-sm text-midnight/70 mt-1">{{ r.details }}</p>
                    </div>
                    <div class="space-x-2">
                        <button @click="resolve(r.id, 'resolved')" class="text-xs text-teal">Resolve</button>
                        <button @click="resolve(r.id, 'dismissed')" class="text-xs text-red-600">Dismiss</button>
                    </div>
                </div>
            </div>
            <p v-if="reports.length === 0" class="text-midnight/50 text-sm">No reports.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const reports = ref<any[]>([]);

async function load() {
    const { data } = await api.get('/api/v1/admin/reports');
    reports.value = data.data;
}

async function resolve(id: number, status: string) {
    await api.put(`/api/v1/admin/reports/${id}`, { status });
    await load();
}

onMounted(load);
</script>
