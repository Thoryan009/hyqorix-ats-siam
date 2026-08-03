import { createRouter, createWebHistory } from 'vue-router'
import { getToken, getUserPermissions } from '@/shared/utils/auth'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

// 1. Glob all routes files inside modules
const moduleRouteFiles = import.meta.glob('../modules/**/routes.js', { eager: true })
const financeRouteFiles = import.meta.glob('../finance/**/routes.js', { eager: true })
const routeFiles = { ...moduleRouteFiles, ...financeRouteFiles }

// 2. Collect all routes
const moduleRoutes = Object.values(routeFiles).flatMap((mod) => {
  // mod may have named exports like { countryRoutes }, we extract all arrays
  return Object.values(mod).flat()
})

const routes = [
  ...moduleRoutes,
  {
    path: '/',
    name: 'home',
    component: () => import('@/modules/home/pages/HomePage.vue'),
  },
  {
    path: '/unauthorized',
    name: 'unauthorized',
    component: () => import('@/shared/pages/UnauthorizedPage.vue'),
  },
    {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/shared/pages/NotFoundPage.vue'),
  },
  //   {
  //   path: '/software',
  //   name: 'software',
  //   component: () => import('@/shared/pages/SoftwarePage.vue'),
  // },
  {
    path: '/software',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '/info',
        name: 'Software Info',
        component: () => import('@/shared/pages/SoftwarePage.vue'),
        // meta: {
        //   permissions: ['application.view'],
        // },
      },
    ],
  }

]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// 3. Global guards
router.beforeEach((to, from, next) => {
  const token = getToken()

  // ✅ Cache permissions ONCE here
  const userPermissions = getUserPermissions()

  // Guest pages
  if (to.meta.guest && token) {
    return next({ name: 'Dashboard' })
  }

  // Requires auth
  if (to.meta.requiresAuth && !token) {
    return next({ name: 'Login' })
  }

  // 🔥 Permission check using cached value
  if (to.meta.permissions) {
    const hasPermission = to.meta.permissions.some((perm) =>
      userPermissions.includes(perm)
    )

    if (!hasPermission) {
      return next({ name: 'unauthorized' })
    }
  }

  next()
})

export default router
