import AgentPage from './pages/AgentPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'
import AgentPerformancePage from './pages/AgentPerformancePage.vue'

export default [
  {
    path: '/agents',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Agent Management',
        component: AgentPage,
        meta: {
          permissions: ['agent.view'],
        },
      },
         {
              path: '/agent-performance',
              name: 'Agent Performance',
              component: AgentPerformancePage,
              meta: {
                permissions: ['agent.view_performance'],
              },
          },
    ],
  },
]
