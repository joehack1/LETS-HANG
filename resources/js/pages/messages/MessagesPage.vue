<template>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 h-[calc(100vh-6rem)]">
        <div class="grid md:grid-cols-3 gap-4 h-full">
            <aside class="bg-white rounded-2xl shadow-sm border border-midnight/5 overflow-hidden flex flex-col">
                <div class="p-4 border-b border-midnight/5">
                    <h2 class="font-bold text-midnight">Conversations</h2>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <button
                        v-for="c in conversations"
                        :key="c.id"
                        @click="select(c)"
                        :class="['w-full text-left p-4 border-b border-midnight/5 hover:bg-warm-white', selected?.id === c.id ? 'bg-warm-white' : '']"
                    >
                        <p class="font-semibold text-midnight">{{ conversationTitle(c) }}</p>
                        <p class="text-xs text-midnight/50 truncate">{{ c.last_message?.body || 'No messages' }}</p>
                        <span v-if="c.unread_count" class="text-xs text-coral font-medium">{{ c.unread_count }} unread</span>
                    </button>
                    <p v-if="conversations.length === 0" class="p-4 text-sm text-midnight/50">No conversations yet.</p>
                </div>
            </aside>

            <section class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-midnight/5 flex flex-col">
                <div v-if="selected" class="flex-1 flex flex-col">
                    <div class="p-4 border-b border-midnight/5">
                        <h2 class="font-bold text-midnight">{{ conversationTitle(selected) }}</h2>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-for="m in messages" :key="m.id" :class="['flex', m.sender.public_id === auth.user?.public_id ? 'justify-end' : 'justify-start']">
                            <div :class="['max-w-[70%] px-4 py-2 rounded-2xl text-sm', m.sender.public_id === auth.user?.public_id ? 'bg-coral text-white' : 'bg-warm-white text-midnight']">
                                <p v-if="m.body">{{ m.body }}</p>
                                <img v-if="m.attachment_url" :src="m.attachment_url" class="mt-2 rounded-xl max-w-full" />
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-midnight/5 flex gap-2">
                        <input v-model="draft" @keyup.enter="send" placeholder="Type a message..." class="flex-1 px-4 py-2 rounded-xl border border-midnight/20 focus:border-coral outline-none" />
                        <button @click="send" class="px-5 py-2 rounded-xl bg-coral text-white font-semibold hover:bg-coral/90 transition">Send</button>
                    </div>
                </div>
                <div v-else class="flex-1 flex items-center justify-center text-midnight/50">Select a conversation</div>
            </section>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import echo from '@/services/echo';

const auth = useAuthStore();
const conversations = ref<any[]>([]);
const selected = ref<any>(null);
const messages = ref<any[]>([]);
const draft = ref('');

async function loadConversations() {
    const { data } = await api.get('/api/v1/conversations');
    conversations.value = data.data;
}

function conversationTitle(c: any) {
    if (c.title) return c.title;
    return c.participants.map((p: any) => p.name).join(', ') || 'Conversation';
}

async function select(c: any) {
    selected.value = c;
    const { data } = await api.get(`/api/v1/conversations/${c.id}/messages`);
    messages.value = data.data;
    await api.post(`/api/v1/conversations/${c.id}/read`);

    // subscribe to real-time
    echo.private(`conversation.${c.id}`).listen('.message.sent', (e: any) => {
        messages.value.push(e.message);
    });
}

async function send() {
    if (!draft.value.trim() || !selected.value) return;
    const { data } = await api.post(`/api/v1/conversations/${selected.value.id}/messages`, { body: draft.value });
    messages.value.push(data.message);
    draft.value = '';
    await api.post(`/api/v1/conversations/${selected.value.id}/read`);
}

onMounted(async () => {
    await loadConversations();
});
</script>
