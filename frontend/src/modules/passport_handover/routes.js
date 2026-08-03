import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import PassportHandoverPage from './pages/PassportHandoverPage.vue'
import PassportHandoverDetailsPage from './pages/PassportHandoverDetailsPage.vue'

export default [
  {
    path: '/passport-handover',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Passport Handover',
        component: PassportHandoverPage,
        meta: {
          permissions: ['passport_handover.view'],
        },
      },
      {
        path: ':id',
        name: 'Passport Handover Details',
        component: PassportHandoverDetailsPage,
        meta: {
          permissions: ['passport_handover.view'],
        },
      },
    ],
  },
]
