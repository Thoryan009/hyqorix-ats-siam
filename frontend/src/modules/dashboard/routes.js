import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import DashboardPage from './pages/DashboardPage.vue'
import FlightSummaryPage from './pages/FlightSummaryPage.vue'
import FlightSummaryPrintPage from './pages/FlightSummaryPrintPage.vue'
export default [
  {
    path: '/dashboard',
    component: DashboardLayout,
    meta: {
      requiresAuth: true,
      roles: ['super_admin', 'admin', 'recruiter', 'accountant', 'client', 'agent'],
    },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: DashboardPage,
      },
    ],
  },
  {
    path: '/dashboard/flight-summary',
    component: DashboardLayout,
    meta: {
      requiresAuth: true,
      roles: ['super_admin', 'admin', 'recruiter', 'accountant', 'client', 'agent'],
  }, children: [
      {
        path: '',
        name: 'FlightSummary',
        component: FlightSummaryPage,
        meta: { permissions: ['flight_summary.view'] },
      },
    ],
  },
    {
          path: '/dashboard/flight-summary/print',
          name: 'Flight Summary Print',
          component: FlightSummaryPrintPage,
          meta: { permissions: ['flight_summary.export'] },
    },
]
