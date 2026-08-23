import JournalsPage from './pages/JournalsPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/journals',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Journal Management',
        component: JournalsPage,
      },
    ],
  },
]
