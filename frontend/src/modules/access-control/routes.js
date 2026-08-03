import permissionPage from './pages/permissionPage.vue'
import RolePage from './pages/RolePage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/employees/roles',
    component: DashboardLayout,
    meta: {
      requiresAuth: true,
    },
    children: [
      {
        path: '',
        name: 'Role List',
        component: RolePage,
        meta: {
          permissions: ['role.view'],
        },
      },

      {
        path: '/employees/permissions',
        name: 'Permission List',
        component: permissionPage,
        meta: {
          permissions: ['permission.view'],
        },
      },
    ],
  },
]
