import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import TasheerAppointmentReportPage from './pages/TasheerAppointmentReportPage.vue'

export default [
  {
    path: '/tasheer-appointment-reports',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Tasheer Appointment Reports',
        component: TasheerAppointmentReportPage,
        meta: { permissions: ['visa_processing.view'] },
      },
    ],
  },
]
