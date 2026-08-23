import ChartOfAccountsPage from './pages/ChartOfAccountsPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/accounts/chart-of-accounts',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Chart of Accounts',
        component: ChartOfAccountsPage,
      },
    ],
  },
]
