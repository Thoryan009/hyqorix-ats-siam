import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import AtsSummaryReportPage from './pages/AtsSummaryReportPage.vue'
import AtsSummaryReportPrintPage from './pages/AtsSummaryReportPrintPage.vue'

export default [
  {
    path: '/ats-summary-report',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'ATS Summary Report',
        component: AtsSummaryReportPage,
        meta: { permissions: ['ats_summary_report.view'] },
      },
    ],

  },
  {
        path: '/ats-summary-report/print',
        name: 'ATS Summary Report Print',
        component: AtsSummaryReportPrintPage,
        meta: { permissions: ['ats_summary_report.view'] },
      },
]
