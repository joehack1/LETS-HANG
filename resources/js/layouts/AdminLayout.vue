<template>
    <div class="min-h-screen bg-warm-white flex">
        <aside class="w-64 bg-midnight text-warm-white p-6 hidden md:block">
            <h2 class="font-black text-xl mb-6">Admin</h2>
            <nav class="space-y-2 text-sm">
                <router-link to="/admin" class="block px-3 py-2 rounded-lg hover:bg-white/10">Dashboard</router-link>
                <router-link to="/admin/users" class="block px-3 py-2 rounded-lg hover:bg-white/10">Users</router-link>
                <router-link to="/admin/activities" class="block px-3 py-2 rounded-lg hover:bg-white/10">Activities</router-link>
                <router-link to="/admin/communities" class="block px-3 py-2 rounded-lg hover:bg-white/10">Communities</router-link>
                <router-link to="/admin/reports" class="block px-3 py-2 rounded-lg hover:bg-white/10">Reports</router-link>
            </nav>
        </aside>
        <main class="flex-1 p-6">
            <router-view />
        </main>
    </div>
</template>

<script setup lang="ts"></script>
