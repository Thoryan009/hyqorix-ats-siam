import AtsPage from './pages/AtsPage.vue'
import SingleAtsPage from './pages/SingleAtsPage.vue'
import JobPage from './pages/JobPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import JobDetailsHeadPage from './pages/JobDetailsHeadPage.vue'
import JobDetailsCategoryPage from './pages/JobDetailsCategoryPage.vue'
import JobDetailsPage from './pages/JobDetailsPage.vue'

export default [
  {
    path: '/jobs',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Job Management',
        component: JobPage,
        meta: {
          permissions: ['job.view'],
        },
      },
      {
        path: ':id/details',
        name: 'Job Details Management',
        component: JobDetailsPage,
        meta: {
          permissions: ['job_detail.view'],
        },
      },
      {
        path: 'ats',
        name: 'ATS',
        component: AtsPage,
        meta: {
          permissions: ['ats.view'],
        },
      },
      {
        path: ':id/ats',
        name: 'Single ATS',
        component: SingleAtsPage,
        meta: {
          permissions: ['ats.view'],
        },
      },
      {
        path: 'job-details-heads',
        name: 'Price Head Management',
        component: JobDetailsHeadPage,
        meta: {
          permissions: ['fee_head.view'],
        },
      },
      {
        path: 'job-details-categories',
        name: 'Price Category Management',
        component: JobDetailsCategoryPage,
        meta: {
          permissions: ['fee_category.view'],
        },
      },
    ],
  },
]
