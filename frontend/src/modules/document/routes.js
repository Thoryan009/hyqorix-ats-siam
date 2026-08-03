import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import DocumentPage from './pages/DocumentPage.vue'

export default [
  {
    path: '/documents',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Document Management',
        component: DocumentPage,
        meta: {
          permissions: ['document.view'],
        },
      },
    ],
  },
]
