import SettingPage from './pages/SettingPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/settings',
    component: DashboardLayout,
    meta: { requiresAuth: true, roles: ['super_admin', 'admin', 'recruiter', 'accountant'] },
    children: [
      {
        path: '',
        name: 'Setting',
        component: SettingPage,
      },
    ],
  },
]
