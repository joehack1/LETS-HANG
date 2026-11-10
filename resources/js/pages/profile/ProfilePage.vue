<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-black text-midnight mb-6">My Profile</h1>

        <div class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6 space-y-6">
            <div class="flex items-center gap-4">
                <div class="h-20 w-20 rounded-full bg-midnight/10 overflow-hidden flex items-center justify-center">
                    <img v-if="profile?.avatar_url" :src="profile.avatar_url" class="h-full w-full object-cover" />
                    <span v-else class="text-2xl font-bold text-midnight/40">{{ initials }}</span>
                </div>
                <div>
                    <label class="px-4 py-2 rounded-xl border border-midnight/20 hover:border-coral cursor-pointer text-sm">
                        Upload avatar
                        <input type="file" accept="image/*" class="hidden" @change="onAvatar" />
                    </label>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Display name</label>
                    <input v-model="form.display_name" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Area / neighborhood</label>
                    <input v-model="form.area" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Bio</label>
                <textarea v-model="form.bio" rows="3" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Interests</label>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="interest in interests"
                        :key="interest.id"
                        type="button"
                        @click="toggleInterest(interest.id)"
                        :class="[
                            'px-3 py-1 rounded-full text-sm border transition',
                            selectedInterests.includes(interest.id)
                                ? 'bg-coral text-white border-coral'
                                : 'border-midnight/20 text-midnight hover:border-coral',
                        ]"
                    >
                        {{ interest.name }}
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.is_discoverable" type="checkbox" />
                <span>Make my profile discoverable</span>
            </label>

            <button @click="save" :disabled="saving" class="px-6 py-3 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Save profile' }}
            </button>
            <p v-if="message" class="text-sm text-teal">{{ message }}</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6 mt-6 space-y-4">
            <h2 class="text-xl font-bold text-midnight">Preferences</h2>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="prefs.friendship_enabled" type="checkbox" />
                <span>Friendship enabled</span>
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="prefs.dating_enabled" type="checkbox" />
                <span>Dating enabled (optional)</span>
            </label>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="prefs.discoverable" type="checkbox" />
                <span>Discoverable</span>
            </label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Min age</label>
                    <input v-model.number="prefs.age_min" type="number" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Max age</label>
                    <input v-model.number="prefs.age_max" type="number" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                </div>
            </div>
            <button @click="savePrefs" class="px-6 py-3 rounded-xl bg-midnight text-white font-semibold hover:bg-midnight/90 transition">Save preferences</button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import api from '@/services/api';

const profile = ref<any>(null);
const interests = ref<any[]>([]);
const selectedInterests = ref<number[]>([]);
const saving = ref(false);
const message = ref('');

const form = reactive({
    display_name: '',
    bio: '',
    area: '',
    is_discoverable: true,
});

const prefs = reactive({
    friendship_enabled: true,
    dating_enabled: false,
    discoverable: true,
    age_min: null as number | null,
    age_max: null as number | null,
    distance_km: null as number | null,
    preferred_cities: [] as string[],
    preferred_intentions: [] as string[],
});

const initials = computed(() => {
    const n = profile.value?.display_name || profile.value?.name || '';
    return n.charAt(0).toUpperCase();
});

onMounted(async () => {
    const [p, i] = await Promise.all([
        api.get('/api/v1/profile'),
        api.get('/api/v1/interests'),
    ]);
    profile.value = p.data.profile;
    interests.value = i.data.interests;
    form.display_name = profile.value.display_name || '';
    form.bio = profile.value.bio || '';
    form.area = profile.value.area || '';
    form.is_discoverable = profile.value.is_discoverable;
    selectedInterests.value = profile.value.interests.map((x: any) => x.id);
    Object.assign(prefs, profile.value.preferences || {});
});

function toggleInterest(id: number) {
    const idx = selectedInterests.value.indexOf(id);
    if (idx === -1) selectedInterests.value.push(id);
    else selectedInterests.value.splice(idx, 1);
}

async function save() {
    saving.value = true;
    message.value = '';
    try {
        const { data } = await api.put('/api/v1/profile', {
            display_name: form.display_name,
            bio: form.bio,
            area: form.area,
            is_discoverable: form.is_discoverable,
            interests: selectedInterests.value,
        });
        profile.value = data.profile;
        message.value = 'Profile saved.';
    } finally {
        saving.value = false;
    }
}

async function savePrefs() {
    await api.put('/api/v1/profile/preferences', { ...prefs });
    message.value = 'Preferences saved.';
}

async function onAvatar(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('avatar', file);
    const { data } = await api.post('/api/v1/profile/avatar', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
    });
    if (profile.value) profile.value.avatar_url = data.avatar_url;
}
</script>
