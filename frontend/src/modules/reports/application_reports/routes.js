import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import ApplicationReportsPage from './pages/ApplicationReportsPage.vue'
import ApplicationReportPage from './pages/ApplicationReportPage.vue'
import ApplicationReportPrintPage from './pages/ApplicationReportPrintPage.vue'


export default [
  {
    path: '/application-reports',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Applicant Reports',
        component: ApplicationReportsPage,
        meta: { permissions: ['application_report.view'] },
      },
      {
        path: ':type',
        name: 'Applicant Report',
        component: ApplicationReportPage,
        meta: { permissions: ['application_report.view'] },
      },
    ],
  },
  {
    path: '/application-reports/:type/print',
    name: 'Application Report Print',
    component: ApplicationReportPrintPage,
    meta: { requiresAuth: true, permissions: ['application_report.view'] },
  },
]
