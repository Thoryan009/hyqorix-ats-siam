import BalanceSheetPage from './pages/BalanceSheetPage.vue'
import ChartOfAccountsPage from './pages/ChartOfAccountsPage.vue'
import GrossProfitPage from './pages/GrossProfitPage.vue'
import IncomeStatementPage from './pages/IncomeStatementPage.vue'
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
  {
    path: '/accounts/gross-profit',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Finance2 Gross Profit',
        component: GrossProfitPage,
      },
    ],
  },
  {
    path: '/accounts/income-statement',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Finance2 Income Statement',
        component: IncomeStatementPage,
      },
    ],
  },
  {
    path: '/accounts/balance-sheet',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Finance2 Balance Sheet',
        component: BalanceSheetPage,
      },
    ],
  },
]
