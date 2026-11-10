import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import PublicLayout from '@/layouts/PublicLayout.vue';
import LandingPage from '@/pages/public/LandingPage.vue';
import LoginPage from '@/pages/auth/LoginPage.vue';
import RegisterPage from '@/pages/auth/RegisterPage.vue';
import ForgotPasswordPage from '@/pages/auth/ForgotPasswordPage.vue';
import ResetPasswordPage from '@/pages/auth/ResetPasswordPage.vue';
import VerifyPage from '@/pages/auth/VerifyPage.vue';
import DashboardPage from '@/pages/dashboard/DashboardPage.vue';
import ProfilePage from '@/pages/profile/ProfilePage.vue';
import PeoplePage from '@/pages/discover/PeoplePage.vue';
import PublicProfilePage from '@/pages/discover/PublicProfilePage.vue';

const routes = [
    {
        path: '/',
        component: PublicLayout,
        children: [
            { path: '', name: 'home', component: LandingPage },
            { path: 'login', name: 'login', component: LoginPage, meta: { guest: true } },
            { path: 'register', name: 'register', component: RegisterPage, meta: { guest: true } },
            { path: 'forgot-password', name: 'forgot-password', component: ForgotPasswordPage, meta: { guest: true } },
            { path: 'reset-password', name: 'reset-password', component: ResetPasswordPage, meta: { guest: true } },
        ],
    },
    {
        path: '/verify',
        name: 'verify',
        component: VerifyPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: DashboardPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/profile',
        name: 'profile',
        component: ProfilePage,
        meta: { requiresAuth: true },
    },
    {
        path: '/people',
        name: 'people',
        component: PeoplePage,
        meta: { requiresAuth: true },
    },
    {
        path: '/people/:public_id',
        name: 'person',
        component: PublicProfilePage,
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();
    if (!auth.initialized) {
        await auth.fetchMe();
    }

    if (to.meta.requiresAuth && !auth.user) {
        return { name: 'login' };
    }

    if (to.meta.guest && auth.user) {
        return { name: 'dashboard' };
    }
});

export default router;
