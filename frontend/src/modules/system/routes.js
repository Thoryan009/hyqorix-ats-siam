import ActivityLogPage from './pages/ActivityLogPage.vue'
import TagPage from './pages/TagPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/settings',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      // {
      //   path: '',
      //   name: 'Setting',
      //   component: SettingPage,
      //   meta: {
      //     permissions: ['setting.view'],
      //   },
      // },

      {
        path: '/employees/activity-logs',
        name: 'ActivityLog List',
        component: ActivityLogPage,
        meta: {
          permissions: ['activity.view'],
        },
      },
      {
        path: '/tags',
        name: 'Tag List',
        component: TagPage,
        meta: {
          permissions: ['tag.view'],
        },
      },
    ],
  },
]
