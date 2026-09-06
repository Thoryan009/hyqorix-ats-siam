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
        meta: {
          permissions: ['chart_of_account.view']
        },
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
        meta: {
          permissions: ['accounts.trial_balance']
        },
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
        meta: {
          permissions: ['accounts.gross_profit']
        },
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
        meta: {
          permissions: ['accounts.income_statement']
        },
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
        meta: {
          permissions: ['accounts.balance_sheet']
        },
      },
    ],
  },
]
