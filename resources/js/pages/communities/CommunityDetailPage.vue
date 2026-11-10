<template>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div v-if="loading" class="text-midnight/60">Loading...</div>
        <div v-else-if="community" class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h1 class="text-3xl font-black text-midnight">{{ community.name }}</h1>
                        <p class="text-sm text-midnight/50">{{ community.members_count }} members · {{ community.visibility }}</p>
                    </div>
                    <button v-if="!community.is_member" @click="join" class="px-5 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Join</button>
                    <button v-else @click="leave" class="px-5 py-2 rounded-xl border border-midnight/20 text-sm">Leave</button>
                </div>
                <p class="mt-4 text-midnight/70">{{ community.description }}</p>
            </div>

            <div v-if="community.is_member" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-6 space-y-4">
                <h2 class="text-xl font-bold text-midnight">Create post</h2>
                <textarea v-model="newPost" rows="3" class="w-full px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" placeholder="Share something..."></textarea>
                <button @click="createPost" class="px-5 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Post</button>
            </div>

            <div class="space-y-4">
                <div v-for="post in posts" :key="post.id" class="bg-white rounded-2xl shadow-sm border border-midnight/5 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-midnight">{{ post.author.name }}</span>
                        <span class="text-xs text-midnight/50">{{ new Date(post.created_at).toLocaleString() }}</span>
                    </div>
                    <p class="text-midnight/70">{{ post.content }}</p>
                    <div v-if="post.comments?.length" class="mt-3 space-y-1 border-t border-midnight/5 pt-3">
                        <div v-for="c in post.comments" :key="c.id" class="text-sm">
                            <span class="font-medium text-midnight">{{ c.author }}:</span>
                            <span class="text-midnight/70 ml-1">{{ c.content }}</span>
                        </div>
                    </div>
                    <div v-if="community.is_member" class="mt-3 flex gap-2">
                        <input v-model="commentText[post.id]" placeholder="Comment..." class="flex-1 px-3 py-1 rounded-xl border border-midnight/20 focus:border-coral outline-none text-sm" />
                        <button @click="addComment(post.id)" class="px-3 py-1 rounded-xl bg-midnight text-white text-sm">Reply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const community = ref<any>(null);
const posts = ref<any[]>([]);
const newPost = ref('');
const commentText = reactive<Record<number, string>>({});
const loading = ref(true);

async function load() {
    loading.value = true;
    const { data } = await api.get(`/api/v1/communities/${route.params.slug}`);
    community.value = data.community;
    const p = await api.get(`/api/v1/communities/${route.params.slug}/posts`);
    posts.value = p.data.data;
    loading.value = false;
}

async function join() {
    await api.post(`/api/v1/communities/${route.params.slug}/join`);
    await load();
}

async function leave() {
    await api.post(`/api/v1/communities/${route.params.slug}/leave`);
    await load();
}

async function createPost() {
    await api.post(`/api/v1/communities/${route.params.slug}/posts`, { content: newPost.value });
    newPost.value = '';
    await load();
}

async function addComment(postId: number) {
    await api.post(`/api/v1/communities/${route.params.slug}/posts/${postId}/comments`, { content: commentText[postId] });
    commentText[postId] = '';
    await load();
}

onMounted(load);
</script>
