import { createRouter, createWebHistory } from 'vue-router';
import Login from './components/Login.vue';
import ResetPassword from "./components/ResetPassword.vue";
import AdminLayout from "./components/AdminLayout.vue";
import AdminDashboard from './components/AdminDashboard.vue';
import AdminChat from "./components/AdminChat.vue";
import AdminUserList from "./components/AdminUserList.vue";
import UserLayout from "./components/UserLayout.vue";
import UserDashboard from './components/UserDashboard.vue';
import EventList from "./components/EventList.vue";
import Profile from './components/Profile.vue';
const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', component: Login, meta: { guest: true } },
    { path: '/password-reset', component: ResetPassword, meta: { guest: true } },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, role: 'admin' },
        children: [
            { path: 'dashboard', component: AdminDashboard },
            { path: 'chat', component: AdminChat },
            { path: 'users', component: AdminUserList },
            { path: 'eventList', component: EventList },
            { path: 'profile', component: Profile }
        ]
    },
    {
        path: '/',
        component: UserLayout,
        meta: { requiresAuth: true, role: 'user' },
        children: [
            { path: 'dashboard', component: UserDashboard },
            { path: 'eventList', component: EventList, props: route => ({ mode: route.query.mode || 'all' }) },
            { path: 'profile', component: Profile }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');

    if (to.matched.some(record => record.meta.requiresAuth)) {
        if (!token) {
            next('/login');
        } else if (to.matched.some(record => record.meta.role === 'admin') && user.role !== 'admin') {
            next('/dashboard');
        } else {
            next();
        }
    }
    // 2. Ha bejelentkezve akar a loginra menni
    else if (to.matched.some(record => record.meta.guest) && token) {
        next(user.role === 'admin' ? '/admin/dashboard' : '/dashboard');
    }
    else {
        next();
    }
});

export default router;