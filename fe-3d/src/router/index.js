import { createRouter, createWebHistory } from "vue-router"; // cài vue-router: npm install vue-router@next --save
import { useAuthStore } from "../store/authStore";
import { useToast } from "vue-toastification";

const routes = [
    {
        path: '/',
        component: () => import('../components/Client/Home.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/san-pham',
        component: () => import('../components/Client/Products.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/product/:id',
        component: () => import('../components/Client/ProductDetail.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/:slug-i.:id(\\d+)',
        component: () => import('../components/Client/ProductDetail.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/thong-tin-ca-nhan',
        component: () => import('../components/Client/Profile.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/gio-hang',
        component: () => import('../components/Client/Cart.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/thanh-toan',
        component: () => import('../components/Client/Checkout.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/kho-voucher',
        component: () => import('../components/Client/VoucherStore.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/lien-he',
        component: () => import('../components/Client/Contact.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/blog',
        component: () => import('../components/Client/BlogList.vue'),
        meta: { layout: 'client' },
    },
    {
        path: '/blog/:id',
        component: () => import('../components/Client/BlogDetail.vue'),
        meta: { layout: 'client' },
    },
//---------------------------------------------athu------------------------------------------------
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
//-----------------------------------------------nhân viên---------------------------------------------------
    {
        path: '/nhan-vien',
        component: () => import('../layout/wrapper/client_admin.vue'),
        children: [
            { path: 'dashboard', component: () => import('../components/Admin/Dashboard.vue'), meta: { title: 'Dashboard', roles: [1, 2] } },
            { path: 'analytics', component: () => import('../components/Admin/Analytics.vue'), meta: { title: 'Thống kê', roles: [1, 2] } },
            { path: 'products', component: () => import('../components/Admin/Products.vue'), meta: { title: 'Sản phẩm', roles: [1, 2, 4, 5] } },
            { path: 'categories', component: () => import('../components/Admin/Categories.vue'), meta: { title: 'Danh mục', roles: [1, 2, 4] } },
            { path: 'orders', component: () => import('../components/Admin/Orders.vue'), meta: { title: 'Đơn hàng', roles: [1, 2, 4, 5] } },
            { path: 'customers', component: () => import('../components/Admin/Customers.vue'), meta: { title: 'Khách hàng', roles: [1, 2, 5] } },
            { path: 'customers/reviews', component: () => import('../components/Admin/Reviews.vue'), meta: { title: 'Đánh giá khách hàng', roles: [1, 2, 5] } },
            { path: 'promotions', component: () => import('../components/Admin/Promotions.vue'), meta: { title: 'Khuyến mãi', roles: [1, 2, 5] } },
            { path: 'staff', component: () => import('../components/Admin/Staff.vue'), meta: { title: 'Nhân viên', roles: [1] } },
            { path: 'blog', component: () => import('../components/Admin/Blog.vue'), meta: { title: 'Quản lý bài viết', roles: [1, 2] } },
            { path: 'profile', component: () => import('../components/Admin/Profile.vue'), meta: { title: 'Thông tin cá nhân', roles: [1, 2, 4, 5] } },
            { path: 'settings', component: () => import('../components/Admin/Settings.vue'), meta: { title: 'Cài đặt hệ thống', roles: [1] } },
            { path: 'error-403', component: () => import('../components/Admin/Error403.vue'), meta: { title: 'Access Denied', roles: [1, 2, 4, 5] } },
        ],
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

router.beforeEach(async (to, from, next) => {
    // 1. Initial Load Query Cleaner
    if (isInitialLoad) {
        isInitialLoad = false;
        if (to.query.search) {
            const newQuery = { ...to.query };
            delete newQuery.search;
            return next({ path: to.path, query: newQuery, replace: true });
        }
    }

    // 2. Role-Based Access Control for Admin routes
    if (to.path.startsWith('/nhan-vien')) {
        const authStore = useAuthStore();
        
        // Validate token if not already validated
        if (!authStore.isValidated) {
            await authStore.validateAdminToken();
        }

        // If not authenticated, redirect to login
        if (!authStore.tokenAdmin) {
            const toast = useToast();
            toast.error("Vui lòng đăng nhập tài khoản Quản trị/Nhân viên!");
            return next('/login');
        }

        // Allow access to error-403 page without further role check
        if (to.path === '/nhan-vien/error-403') {
            return next();
        }

        // Check if route has roles configuration
        if (to.meta && to.meta.roles) {
            if (!to.meta.roles.includes(authStore.vaiTro)) {
                // Insufficient privileges -> redirect to error-403
                return next('/nhan-vien/error-403');
            }
        }
    }

    next();
});

export default router