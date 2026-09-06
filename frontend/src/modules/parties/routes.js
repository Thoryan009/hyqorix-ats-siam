import PartiesPage from './pages/PartiesPage.vue'
import PartyTypesPage from './pages/PartyTypesPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/parties',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Party Management',
        component: PartiesPage,
        meta: {
          permissions: ['party.view']
        },
      },
    ],
  },
  {
    path: '/party-types',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Party Type Management',
        component: PartyTypesPage,
        meta: {
          permissions: ['party_type.view']
        },
      },
    ],
  },
]
