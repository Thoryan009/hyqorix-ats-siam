import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import TransactionReportPrintPage from './pages/TransactionReportPrintPage.vue'
import TransactionReportsPage from './pages/TransactionReportsPage.vue'
import TransactionReportPage from './pages/TransactionReportPage.vue'

export default [
  {
    path: '/transaction-reports',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Transaction Reports',
        component: TransactionReportsPage,
        meta: { permissions: ['transaction_report.view'] },
      },
      {
        path: ':type',
        name: 'Transaction Report',
        component: TransactionReportPage,
        meta: { permissions: ['transaction_report.view'] },
      },
    ],
  },
  {
    path: '/transaction-reports/:type/print',
    name: 'Transaction Report Print',
    component: TransactionReportPrintPage,
    meta: { permissions: ['transaction_report.view'] },
  },
]
