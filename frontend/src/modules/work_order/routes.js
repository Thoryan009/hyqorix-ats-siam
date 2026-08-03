import WorkOrderPage from './pages/WorkOrderPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/work-orders',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Demand Letter Management',
        component: WorkOrderPage,
        meta: {
          permissions: ['demand_letter.view'],
        },
      },
    ],
  },
]
