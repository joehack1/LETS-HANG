<template>
    <div class="bg-white rounded-2xl shadow-sm border border-midnight/5 overflow-hidden hover:shadow-lg transition">
        <div class="aspect-[16/9] bg-gradient-to-br from-teal to-sunshine flex items-center justify-center text-white font-bold">
            <img v-if="activity.cover_url" :src="activity.cover_url" class="w-full h-full object-cover" />
            <span v-else>{{ activity.title }}</span>
        </div>
        <div class="p-4 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-teal">{{ activity.category }}</span>
                <span class="text-xs text-midnight/50">{{ formatDate(activity.starts_at) }}</span>
            </div>
            <h3 class="font-bold text-midnight">{{ activity.title }}</h3>
            <p class="text-sm text-midnight/60 line-clamp-2">{{ activity.description }}</p>
            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-midnight/50">{{ activity.approved_count }}/{{ activity.max_participants }} going</span>
                <router-link :to="`/activities/${activity.public_id}`" class="text-coral text-sm font-medium">View</router-link>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{ activity: any }>();

function formatDate(d: string) {
    return new Date(d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>
