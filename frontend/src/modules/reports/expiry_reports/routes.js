import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import ExpiryReportPage from './pages/ExpiryReportPage.vue'

export default [
  {
    path: '/expiry-reports',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Process Expiry Reports',
        component: ExpiryReportPage,
        meta: { permissions: ['process_expiry_report.view'] },
      },
    ],
  },
  {
    path: '/expiry-reports/print',
    name: 'Process Expiry Report Print',
    component: () => import('./pages/ExpiryReportPrintPage.vue'),
}
]
