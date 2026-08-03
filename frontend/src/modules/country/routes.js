import CountryPage from './pages/CountryPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/countries',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Country Management',
        component: CountryPage,
        meta: {
          permissions: ['country.view'],
        },
      },
    ],
  },
]
