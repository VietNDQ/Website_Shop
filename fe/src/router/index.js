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
        path: '/admin',
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
    routes: routes
})

export default router