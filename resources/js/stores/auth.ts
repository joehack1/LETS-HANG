import { defineStore } from 'pinia';
import { ref } from 'vue';
import api, { ensureCsrfCookie } from '@/services/api';

export interface AuthUser {
    public_id: string;
    name: string;
    email: string;
    phone: string | null;
    role: string;
    account_status: string;
    email_verified: boolean;
    phone_verified: boolean;
    fully_verified: boolean;
    age: number | null;
    country: string | null;
    city: string | null;
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(null);
    const loading = ref(false);
    const initialized = ref(false);

    async function fetchMe(): Promise<void> {
        try {
            const { data } = await api.get('/api/v1/me');
            user.value = data.user;
        } catch {
            user.value = null;
        } finally {
            initialized.value = true;
        }
    }

    async function register(payload: Record<string, unknown>): Promise<void> {
        loading.value = true;
        try {
            await ensureCsrfCookie();
            await api.post('/api/v1/register', payload);
            await fetchMe();
        } finally {
            loading.value = false;
        }
    }

    async function login(payload: { email: string; password: string; remember?: boolean }): Promise<void> {
        loading.value = true;
        try {
            await ensureCsrfCookie();
            await api.post('/api/v1/login', payload);
            await fetchMe();
        } finally {
            loading.value = false;
        }
    }

    async function logout(): Promise<void> {
        await ensureCsrfCookie();
        await api.post('/api/v1/logout');
        user.value = null;
    }

    const isAuthenticated = () => user.value !== null;

    return { user, loading, initialized, fetchMe, register, login, logout, isAuthenticated };
});
