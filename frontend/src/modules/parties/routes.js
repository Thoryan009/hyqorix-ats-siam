import PartiesPage from './pages/PartiesPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/parties',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Party Management',
        component: PartiesPage,
      },
    ],
  },
]
