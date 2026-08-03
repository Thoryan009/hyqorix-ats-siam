import VendorPage from './pages/VendorPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/vendors',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Vendor Management',
        component: VendorPage,
        meta: {
          permissions: ['vendor.view'],
        },
      },
    ],
  },
]
