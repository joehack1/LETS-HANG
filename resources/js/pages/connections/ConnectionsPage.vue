<template>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-6">Connections</h1>

        <div class="flex gap-3 mb-6">
            <button @click="tab = 'incoming'" :class="tab === 'incoming' ? 'bg-coral text-white' : 'bg-white text-midnight'" class="px-4 py-2 rounded-xl border border-midnight/20 font-medium">Incoming</button>
            <button @click="tab = 'outgoing'" :class="tab === 'outgoing' ? 'bg-coral text-white' : 'bg-white text-midnight'" class="px-4 py-2 rounded-xl border border-midnight/20 font-medium">Outgoing</button>
            <button @click="tab = 'friends'" :class="tab === 'friends' ? 'bg-coral text-white' : 'bg-white text-midnight'" class="px-4 py-2 rounded-xl border border-midnight/20 font-medium">Friends</button>
        </div>

        <div v-if="tab === 'incoming' || tab === 'outgoing'" class="space-y-3">
            <div v-for="r in requests" :key="r.id" class="bg-white rounded-2xl border border-midnight/5 p-4 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-midnight">
                        {{ tab === 'incoming' ? r.sender.name : r.receiver.name }}
                    </p>
                    <p class="text-xs text-midnight/50">{{ r.intention }} · {{ r.status }}</p>
                </div>
                <div class="flex gap-2" v-if="tab === 'incoming' && r.status === 'pending'">
                    <button @click="respond(r.id, 'accept')" class="px-3 py-1 rounded-xl bg-teal text-white text-sm">Accept</button>
                    <button @click="respond(r.id, 'decline')" class="px-3 py-1 rounded-xl border border-midnight/20 text-sm">Decline</button>
                </div>
                <button v-if="tab === 'outgoing' && r.status === 'pending'" @click="cancel(r.id)" class="px-3 py-1 rounded-xl border border-midnight/20 text-sm">Cancel</button>
            </div>
            <p v-if="requests.length === 0" class="text-midnight/50 text-sm">No requests.</p>
        </div>

        <div v-if="tab === 'friends'" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="c in connections" :key="c.id" class="bg-white rounded-2xl border border-midnight/5 p-4">
                <p class="font-semibold text-midnight">{{ c.user.display_name || c.user.name }}</p>
                <p class="text-xs text-midnight/50">{{ c.type }}</p>
                <button @click="remove(c.id)" class="mt-3 text-sm text-red-600">Remove</button>
            </div>
            <p v-if="connections.length === 0" class="text-midnight/50 text-sm">No connections yet.</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import api from '@/services/api';

const tab = ref('incoming');
const requests = ref<any[]>([]);
const connections = ref<any[]>([]);

async function loadRequests() {
    const direction = tab.value === 'outgoing' ? 'outgoing' : 'incoming';
    const { data } = await api.get('/api/v1/connections/requests', { params: { direction } });
    requests.value = data.data;
}

async function loadConnections() {
    const { data } = await api.get('/api/v1/connections');
    connections.value = data.data;
}

async function respond(id: number, action: 'accept' | 'decline') {
    await api.post(`/api/v1/connections/requests/${id}/${action}`);
    await loadRequests();
}

async function cancel(id: number) {
    await api.post(`/api/v1/connections/requests/${id}/cancel`);
    await loadRequests();
}

async function remove(id: number) {
    await api.delete(`/api/v1/connections/${id}`);
    await loadConnections();
}

watch(tab, async () => {
    if (tab.value === 'friends') await loadConnections();
    else await loadRequests();
});

onMounted(async () => {
    await loadRequests();
});
</script>
