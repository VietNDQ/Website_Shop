import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save

const routes = [
    {
        path: '/',
        component: () => import('../components/Client/Home.vue'),
        meta: { layout: 'client' }
    },
    {
        path: '/product/:id',
        component: () => import('../components/Client/ProductDetail.vue'),
        meta: { layout: 'client' }
    },
    {
        path: '/thong-tin-ca-nhan',
        component: () => import('../components/Client/Profile.vue'),
        meta: { layout: 'client' }
    },
    {
        path: '/gio-hang',
        component: () => import('../components/Client/Cart.vue'),
        meta: { layout: 'client' }
    },
    {
        path: '/thanh-toan',
        component: () => import('../components/Client/Checkout.vue'),
        meta: { layout: 'client' }
    },
    {
        path: '/login',
        component: () => import('../components/Auth/Login.vue'),
        meta: { layout: 'default' }
    },
    {
        path: '/register',
        component: () => import('../components/Auth/Register.vue'),
        meta: { layout: 'default' }
    },
    {
        path: '/forgot-password',
        component: () => import('../components/Auth/ForgotPassword.vue'),
        meta: { layout: 'default' }
    },
    {
        path: '/reset-password',
        component: () => import('../components/Auth/ResetPassword.vue'),
        meta: { layout: 'default' }
    },
    {
        path: '/nhan-vien',
        component: () => import('../layout/wrapper/client_admin.vue'),
        children: [
            { path: 'dashboard', component: () => import('../components/Admin/Dashboard.vue'), meta: { title: 'Dashboard' } },
            { path: 'analytics', component: () => import('../components/Admin/Analytics.vue'), meta: { title: 'Thống kê' } },
            { path: 'products', component: () => import('../components/Admin/Products.vue'), meta: { title: 'Sản phẩm' } },
            { path: 'categories', component: () => import('../components/Admin/Categories.vue'), meta: { title: 'Danh mục' } },
            { path: 'orders', component: () => import('../components/Admin/Orders.vue'), meta: { title: 'Đơn hàng' } },
            { path: 'customers', component: () => import('../components/Admin/Customers.vue'), meta: { title: 'Khách hàng' } },
            { path: 'promotions', component: () => import('../components/Admin/Promotions.vue'), meta: { title: 'Khuyến mãi' } },
            { path: 'staff', component: () => import('../components/Admin/Staff.vue'), meta: { title: 'Nhân viên' } },
            { path: 'profile', component: () => import('../components/Admin/Profile.vue'), meta: { title: 'Thông tin cá nhân' } },
            { path: 'settings', component: () => import('../components/Admin/Settings.vue'), meta: { title: 'Cài đặt hệ thống' } },
        ]
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return { top: 0, behavior: 'smooth' };
        }
    }
})

let isInitialLoad = true;

router.beforeEach((to, from, next) => {
    if (isInitialLoad) {
        isInitialLoad = false;
        if (to.query.search) {
            const newQuery = { ...to.query };
            delete newQuery.search;
            return next({ path: to.path, query: newQuery, replace: true });
        }
    }
    next();
});

export default router