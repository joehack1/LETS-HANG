<template>
    <button
        @click="send"
        :disabled="loading"
        class="px-4 py-2 rounded-xl bg-coral text-white text-sm font-semibold hover:bg-coral/90 transition disabled:opacity-50"
    >
        {{ loading ? 'Sending...' : 'Connect' }}
    </button>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import api from '@/services/api';

const props = defineProps<{ receiverPublicId: string; intention?: 'friendship' | 'dating' }>();

const loading = ref(false);
const emit = defineEmits<{ (e: 'sent'): void }>();

async function send() {
    loading.value = true;
    try {
        await api.post('/api/v1/connections/requests', {
            receiver_public_id: props.receiverPublicId,
            intention: props.intention || 'friendship',
        });
        emit('sent');
    } finally {
        loading.value = false;
    }
}
</script>
