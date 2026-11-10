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
import ConnectionsPage from '@/pages/connections/ConnectionsPage.vue';
import MessagesPage from '@/pages/messages/MessagesPage.vue';
import NotificationsPage from '@/pages/notifications/NotificationsPage.vue';
import PricingPage from '@/pages/premium/PricingPage.vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminDashboardPage from '@/pages/admin/AdminDashboardPage.vue';
import AdminUsersPage from '@/pages/admin/AdminUsersPage.vue';
import AdminReportsPage from '@/pages/admin/AdminReportsPage.vue';
import ActivitiesPage from '@/pages/activities/ActivitiesPage.vue';
import ActivityDetailPage from '@/pages/activities/ActivityDetailPage.vue';
import CreateActivityPage from '@/pages/activities/CreateActivityPage.vue';
import CommunitiesPage from '@/pages/communities/CommunitiesPage.vue';
import CommunityDetailPage from '@/pages/communities/CommunityDetailPage.vue';
import CreateCommunityPage from '@/pages/communities/CreateCommunityPage.vue';

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
    {
        path: '/connections',
        name: 'connections',
        component: ConnectionsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/activities',
        name: 'activities',
        component: ActivitiesPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/activities/create',
        name: 'activity-create',
        component: CreateActivityPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/activities/:public_id',
        name: 'activity-detail',
        component: ActivityDetailPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/communities',
        name: 'communities',
        component: CommunitiesPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/communities/create',
        name: 'community-create',
        component: CreateCommunityPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/communities/:slug',
        name: 'community-detail',
        component: CommunityDetailPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/messages',
        name: 'messages',
        component: MessagesPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: NotificationsPage,
        meta: { requiresAuth: true },
    },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            { path: '', name: 'admin-dashboard', component: AdminDashboardPage },
            { path: 'users', name: 'admin-users', component: AdminUsersPage },
            { path: 'reports', name: 'admin-reports', component: AdminReportsPage },
        ],
    },
    {
        path: '/premium',
        name: 'premium',
        component: PricingPage,
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

    if (to.meta.requiresAdmin && !auth.user?.role?.match(/admin|moderator/)) {
        return { name: 'dashboard' };
    }

    if (to.meta.guest && auth.user) {
        return { name: 'dashboard' };
    }
});

export default router;
