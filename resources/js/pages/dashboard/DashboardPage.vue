<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-midnight">Dashboard</h1>
                <p class="text-midnight/60">Welcome back, {{ auth.user?.name }}.</p>
            </div>
            <router-link to="/profile" class="px-4 py-2 rounded-xl border border-midnight/20 text-sm font-medium hover:border-coral transition">Edit profile</router-link>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <router-link to="/people" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Discover People</h3>
                <p class="text-sm text-midnight/60 mt-1">Find new friends</p>
            </router-link>
            <router-link to="/activities" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Activities</h3>
                <p class="text-sm text-midnight/60 mt-1">Join or create meetups</p>
            </router-link>
            <router-link to="/communities" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Communities</h3>
                <p class="text-sm text-midnight/60 mt-1">Join groups</p>
            </router-link>
            <router-link to="/connections" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Connections</h3>
                <p class="text-sm text-midnight/60 mt-1">Manage friends</p>
            </router-link>
            <router-link to="/messages" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Messages</h3>
                <p class="text-sm text-midnight/60 mt-1">Chat with people</p>
            </router-link>
            <router-link to="/notifications" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Notifications</h3>
                <p class="text-sm text-midnight/60 mt-1">Stay updated</p>
            </router-link>
            <router-link to="/premium" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Premium</h3>
                <p class="text-sm text-midnight/60 mt-1">Upgrade your plan</p>
            </router-link>
            <router-link v-if="auth.user?.role === 'admin' || auth.user?.role === 'moderator'" to="/admin" class="bg-white rounded-2xl border border-midnight/5 p-5 hover:shadow-lg transition">
                <h3 class="font-bold text-midnight">Admin</h3>
                <p class="text-sm text-midnight/60 mt-1">Manage platform</p>
            </router-link>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6">
            <h2 class="text-xl font-bold text-midnight mb-4">Verification status</h2>
            <p class="text-sm text-midnight/60">Email: <span class="font-medium text-midnight">{{ auth.user?.email_verified ? 'Verified' : 'Pending' }}</span></p>
            <p class="text-sm text-midnight/60">Phone: <span class="font-medium text-midnight">{{ auth.user?.phone_verified ? 'Verified' : 'Pending' }}</span></p>
            <router-link to="/verify" class="inline-block mt-3 text-coral text-sm font-medium">Manage verification →</router-link>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '@/stores/auth';
const auth = useAuthStore();
</script>
