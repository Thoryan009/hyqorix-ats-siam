import LoginPage from './pages/LoginPage.vue'
import ForgotPasswordPage from './pages/ForgotPasswordPage.vue'


export default [
  {
    path: '/login',
    component: LoginPage,
    name: 'Login',
    meta: { guest: true },
  },
  {
    path: '/forgot-password',
    component: ForgotPasswordPage,
    name: 'ForgotPassword',
    meta: { guest: true },
  },
]
