import Vue from 'vue';
import VueRouter from 'vue-router';
import LoginComponent from './components/LoginComponent.vue';
import IndexComponent from './components/IndexComponent.vue';
import AdminComponent from './components/AdminComponent.vue';
import RolesComponent from './components/RolesComponent.vue';
import UsersComponent from './components/UsersComponent.vue';
import ProductsComponent from './components/ProductsComponent.vue';
import CategoriesComponent from './components/CategoriesComponent.vue';
import SettingsComponent from './components/SettingsComponent.vue';
Vue.use(VueRouter)

const routes = [
    {
        path: '/',
        redirect: '/login',
    },
    {
        path: '/login',
        component: LoginComponent,
        name: 'Login',
        beforeEnter: (to, from, next) => {
            if (localStorage.getItem('token')) {
                next('/admin');
            } else {
                next();
            }
        }
    },
    {
        path: '/admin',
        component: AdminComponent,
        name: 'Admin',
        children: [
            {
                path: 'index',
                component: IndexComponent,
                name: 'Index'
            },
            {
                path: 'roles',
                component: RolesComponent,
                name: 'Roles'
            },
            {
                path: 'users',
                component: UsersComponent,
                name: 'Users'
            },
            {
                path: 'products',
                component: ProductsComponent,
                name: 'Products'
            },
            {
                path: 'categories',
                component: CategoriesComponent,
                name: 'Categories'
            },
            {
                path: 'settings',
                component: SettingsComponent,
                name: 'Settings'
            },
        ],
        beforeEnter: (to, from, next) => {
            axios.get('api/verify')
                .then(res => next())
                .catch(err => {
                    localStorage.removeItem('token')
                    next('/login')
                })
        }
    },
]
const router = new VueRouter({ routes })
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token') || null
    window.axios.defaults.headers['Authorization'] = "Bearer " + token;
    next();
})
export default router