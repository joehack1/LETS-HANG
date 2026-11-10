import axios from 'axios';

const api = axios.create({
    baseURL: '/',
    withCredentials: true,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

export async function ensureCsrfCookie(): Promise<void> {
    await api.get('/sanctum/csrf-cookie');
}

export default api;
