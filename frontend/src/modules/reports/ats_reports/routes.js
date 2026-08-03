import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import AtsReportsPage from './pages/AtsReportsPage.vue'
import ATSReportPage from './pages/ATSReportPage.vue'
import AtsReportPrintPage from './pages/AtsReportPrintPage.vue'

export default [
  {
    path: '/ats-reports',
    component: DashboardLayout,
    meta: { requiresAuth: true},
    children: [
      {
        path: '',
        name: 'ATS Reports',
        component: AtsReportsPage,
        meta: { permissions: ['ats_report.view'] },
      },
      {
        path: ':type',
        name: 'ATS Report',
        component: ATSReportPage,
        meta: { permissions: ['ats_report.view'] },
      },
    ],
  },
  {
      path: '/ats-reports/:type/print',
      name: 'ATS Report Print',
      component: AtsReportPrintPage,
      meta: { permissions: ['ats_report.view'] },
    },
]
