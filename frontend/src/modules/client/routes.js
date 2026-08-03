import ClientPage from './pages/ClientPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/clients',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Client Management',
        component: ClientPage,
        meta: {
          permissions: ['client.view'],
        }
      },
    ],
  },
]
