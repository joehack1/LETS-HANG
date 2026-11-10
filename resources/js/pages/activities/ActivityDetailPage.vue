<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div v-if="loading" class="text-midnight/60">Loading...</div>
        <div v-else-if="activity" class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-midnight/5 overflow-hidden">
                <div class="aspect-[16/9] bg-gradient-to-br from-teal to-sunshine flex items-center justify-center text-white text-2xl font-bold">
                    <img v-if="activity.cover_url" :src="activity.cover_url" class="w-full h-full object-cover" />
                    <span v-else>{{ activity.title }}</span>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <h1 class="text-3xl font-black text-midnight">{{ activity.title }}</h1>
                            <p class="text-sm text-midnight/50">{{ activity.category }} · {{ formatDate(activity.starts_at) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button v-if="!activity.is_organizer" @click="join" class="px-5 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">
                                {{ activity.my_status === 'approved' ? 'Joined' : (activity.my_status === 'pending' ? 'Pending' : 'Join') }}
                            </button>
                            <button v-if="activity.my_status && activity.my_status !== 'cancelled'" @click="leave" class="px-5 py-2 rounded-xl border border-midnight/20 text-sm">Leave</button>
                        </div>
                    </div>
                    <p class="text-midnight/70">{{ activity.description }}</p>
                    <div class="grid sm:grid-cols-2 gap-4 text-sm">
                        <div><span class="font-medium">Venue:</span> {{ activity.venue_name || 'TBA' }}</div>
                        <div><span class="font-medium">City:</span> {{ activity.city }}</div>
                        <div><span class="font-medium">Spots:</span> {{ activity.approved_count }}/{{ activity.max_participants }}</div>
                        <div v-if="activity.price"><span class="font-medium">Price:</span> {{ activity.price }} {{ activity.currency }}</div>
                    </div>
                    <div v-if="activity.instructions" class="text-sm text-midnight/70 border-t border-midnight/5 pt-4">
                        <span class="font-medium">Instructions:</span> {{ activity.instructions }}
                    </div>
                </div>
            </div>

            <div v-if="activity.is_organizer" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6">
                <h2 class="text-xl font-bold text-midnight mb-4">Participants</h2>
                <div class="space-y-2">
                    <div v-for="p in participants" :key="p.id" class="flex items-center justify-between py-2 border-b border-midnight/5 last:border-0">
                        <div>
                            <span class="font-medium text-midnight">{{ p.user.name }}</span>
                            <span class="ml-2 text-xs text-midnight/50">{{ p.status }}</span>
                        </div>
                        <div class="flex gap-2" v-if="p.status === 'pending'">
                            <button @click="review(p.user.public_id ?? p.user.id, 'approve')" class="text-sm text-teal">Approve</button>
                            <button @click="review(p.user.public_id ?? p.user.id, 'reject')" class="text-sm text-red-600">Reject</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const activity = ref<any>(null);
const participants = ref<any[]>([]);
const loading = ref(true);

async function load() {
    loading.value = true;
    const { data } = await api.get(`/api/v1/activities/${route.params.public_id}`);
    activity.value = data.activity;
    if (activity.value.is_organizer) {
        const p = await api.get(`/api/v1/activities/${route.params.public_id}/participants`);
        participants.value = p.data.data;
    }
    loading.value = false;
}

async function join() {
    await api.post(`/api/v1/activities/${route.params.public_id}/join`);
    await load();
}

async function leave() {
    await api.post(`/api/v1/activities/${route.params.public_id}/leave`);
    await load();
}

async function review(userId: number, action: 'approve' | 'reject') {
    await api.post(`/api/v1/activities/${route.params.public_id}/participants/${userId}/${action}`);
    await load();
}

function formatDate(d: string) {
    return new Date(d).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}

onMounted(load);
</script>
