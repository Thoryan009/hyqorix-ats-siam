import DepartmentPage from './pages/DepartmentPage.vue'
import EmployeePage from './pages/EmployeePage.vue'
import DesignationPage from './pages/DesignationPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import EmployeePerformancePage from './pages/EmployeePerformancePage.vue'
export default [
  {
    path: '/employees',
    component: DashboardLayout,
    meta: { requiresAuth: true},
    children: [
      {
        path: '',
        name: 'Employee List',
        component: EmployeePage,
        meta: {
          permissions: ['employee.view'],
        }
      },

      {
        path: 'designations',
        name: 'Designation List',
        component: DesignationPage,
        meta: {
          permissions: ['designation.view'],
        }
      },

      {
        path: 'departments',
        name: 'Department List',
        component: DepartmentPage,
        meta: {
          permissions: ['department.view'],
        },
      },

      {
        path: '/employee-performance',
        name: 'Employee Performance',
        component: EmployeePerformancePage,
        meta: {
          permissions: ['employee.view_performance'],
        },
      },
],
  },
]
