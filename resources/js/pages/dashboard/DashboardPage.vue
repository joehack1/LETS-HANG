<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-2">Dashboard</h1>
        <p class="text-midnight/60">Welcome, {{ auth.user?.name }}.</p>

        <div class="mt-8 grid md:grid-cols-3 gap-6">
            <div class="p-6 rounded-2xl bg-white border border-midnight/5 shadow-sm">
                <h3 class="font-bold text-midnight">Verification</h3>
                <p class="text-sm text-midnight/60 mt-1">Email: {{ auth.user?.email_verified ? 'Verified' : 'Pending' }}</p>
                <p class="text-sm text-midnight/60">Phone: {{ auth.user?.phone_verified ? 'Verified' : 'Pending' }}</p>
                <router-link to="/verify" class="text-coral text-sm font-medium mt-3 inline-block">Manage verification</router-link>
            </div>
            <div class="p-6 rounded-2xl bg-white border border-midnight/5 shadow-sm">
                <h3 class="font-bold text-midnight">Activities</h3>
                <p class="text-sm text-midnight/60 mt-1">Coming in Phase 5.</p>
            </div>
            <div class="p-6 rounded-2xl bg-white border border-midnight/5 shadow-sm">
                <h3 class="font-bold text-midnight">Communities</h3>
                <p class="text-sm text-midnight/60 mt-1">Coming in Phase 6.</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
const auth = useAuthStore();
</script>
