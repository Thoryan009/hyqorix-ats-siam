import ChartOfAccountsPage from './pages/ChartOfAccountsPage.vue'
import TrialBalancePage from './pages/TrialBalancePage.vue'
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
  {
    path: '/accounts/trial-balance',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Finance2 Trial Balance',
        component: TrialBalancePage,
      },
    ],
  },
]
