import PrincipalPage from './pages/PrincipalPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/principals',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Principal Management',
        component: PrincipalPage,
        meta: {
          permissions: ['principal.view'],
        },
      },
    ],
  },
]
