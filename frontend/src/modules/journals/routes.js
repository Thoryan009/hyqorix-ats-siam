import GeneralLedgerPage from './pages/GeneralLedgerPage.vue'
import JournalsPage from './pages/JournalsPage.vue'
import JournalViewPage from './pages/JournalViewPage.vue'
import PartyLedgerPage from './pages/PartyLedgerPage.vue'
import PostJournalPage from './pages/PostJournalPage.vue'
import TransactionTypesPage from './pages/TransactionTypesPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

export default [
  {
    path: '/journals',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Journal Management',
        component: JournalsPage,
      },
      {
        path: 'post',
        name: 'Post Journal',
        component: PostJournalPage,
      },
      {
        path: 'party-ledger',
        name: 'Party Ledger',
        component: PartyLedgerPage,
      },
      {
        path: 'general-ledger',
        name: 'General Ledger',
        component: GeneralLedgerPage,
      },
      {
        path: ':id',
        name: 'Journal View',
        component: JournalViewPage,
      },
    ],
  },
  {
    path: '/journal-transaction-types',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Transaction Type Management',
        component: TransactionTypesPage,
      },
    ],
  },
]
