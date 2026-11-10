<template>
    <div>
        <h1 class="text-3xl font-black text-midnight mb-6">Users</h1>
        <div class="bg-white rounded-2xl border border-midnight/5 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-warm-white">
                    <tr>
                        <th class="text-left p-3">Name</th>
                        <th class="text-left p-3">Email</th>
                        <th class="text-left p-3">Role</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id" class="border-t border-midnight/5">
                        <td class="p-3">{{ u.name }}</td>
                        <td class="p-3">{{ u.email }}</td>
                        <td class="p-3">{{ u.role }}</td>
                        <td class="p-3">{{ u.account_status }}</td>
                        <td class="p-3 space-x-2">
                            <button @click="setStatus(u.id, 'suspended')" class="text-xs text-red-600">Suspend</button>
                            <button @click="setStatus(u.id, 'active')" class="text-xs text-teal">Activate</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import api from '@/services/api';

const users = ref<any[]>([]);

async function load() {
    const { data } = await api.get('/api/v1/admin/users');
    users.value = data.data;
}

async function setStatus(id: number, status: string) {
    await api.put(`/api/v1/admin/users/${id}/status`, { status });
    await load();
}

onMounted(load);
</script>
