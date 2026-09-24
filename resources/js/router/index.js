import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../stores/auth';

const routes = [
    { path: '/', name: 'home', component: () => import('../pages/Home.vue'), meta: { layout: 'app' } },
    { path: '/about', name: 'about', component: () => import('../pages/About.vue'), meta: { layout: 'app' } },
    { path: '/contact', name: 'contact', component: () => import('../pages/Contact.vue'), meta: { layout: 'app' } },
    { path: '/login', name: 'login', component: () => import('../pages/Login.vue'), meta: { layout: 'app', guest: true } },
    { path: '/register', name: 'register', component: () => import('../pages/Register.vue'), meta: { layout: 'app', guest: true } },
    { path: '/worker/register', name: 'worker-register', component: () => import('../pages/WorkerRegister.vue'), meta: { layout: 'app', guest: true } },
    { path: '/get-started', name: 'find', component: () => import('../pages/Find.vue'), meta: { layout: 'app' } },
    { path: '/services', redirect: '/get-started' },
    { path: '/workers/nearby', redirect: (to) => ({ path: '/get-started', query: to.query }) },
    { path: '/bookings', name: 'bookings', component: () => import('../pages/Bookings.vue'), meta: { layout: 'app', auth: true } },
    { path: '/worker/dashboard', name: 'worker-dashboard', component: () => import('../pages/WorkerDashboard.vue'), meta: { layout: 'app', auth: true, worker: true } },
    { path: '/admin', name: 'admin-login', component: () => import('../pages/admin/Login.vue'), meta: { guest: true } },
    { path: '/admin/dashboard', name: 'admin-dashboard', component: () => import('../pages/admin/Dashboard.vue'), meta: { admin: true } },
    { path: '/admin/users', name: 'admin-users', component: () => import('../pages/admin/Users.vue'), meta: { admin: true } },
    { path: '/admin/workers', name: 'admin-workers', component: () => import('../pages/admin/Workers.vue'), meta: { admin: true } },
    { path: '/admin/bookings', name: 'admin-bookings', component: () => import('../pages/admin/Bookings.vue'), meta: { admin: true } },
    { path: '/admin/bookings/create', name: 'admin-booking-create', component: () => import('../pages/admin/BookingCreate.vue'), meta: { admin: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach((to) => {
    const auth = useAuth();

    if (to.meta.auth && !auth.isAuthenticated.value) {
        return { name: 'login' };
    }

    if (to.meta.admin && !auth.isAdmin.value) {
        return { name: 'admin-login' };
    }

    if (to.meta.worker && !auth.isWorker.value) {
        return { name: 'worker-register' };
    }

    if (to.meta.guest && auth.isAuthenticated.value) {
        if (auth.isAdmin.value) {
            return { name: 'admin-dashboard' };
        }

        if (auth.isWorker.value) {
            return { name: 'worker-dashboard' };
        }

        return { name: 'find' };
    }

    return true;
});

export default router;
