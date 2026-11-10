<template>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-black text-midnight">Notifications</h1>
            <button @click="markAll" class="text-sm text-coral font-medium">Mark all as read</button>
        </div>
        <div class="space-y-3">
            <div v-for="n in notifications" :key="n.id" :class="['bg-white rounded-2xl border border-midnight/5 p-4', n.read_at ? 'opacity-60' : '']">
                <p class="text-sm text-midnight">{{ n.data.message || n.type }}</p>
                <p class="text-xs text-midnight/50 mt-1">{{ new Date(n.created_at).toLocaleString() }}</p>
                <button v-if="!n.read_at" @click="markRead(n.id)" class="text-xs text-coral mt-1">Mark read</button>
            </div>
            <p v-if="notifications.length === 0" class="text-midnight/50 text-sm">No notifications.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const notifications = ref<any[]>([]);

async function load() {
    const { data } = await api.get('/api/v1/notifications');
    notifications.value = data.data;
}

async function markRead(id: string) {
    await api.post(`/api/v1/notifications/${id}/read`);
    await load();
}

async function markAll() {
    await api.post('/api/v1/notifications/read-all');
    await load();
}

onMounted(load);
</script>
