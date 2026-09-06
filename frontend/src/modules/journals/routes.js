// import { meta } from 'eslint-plugin-vue'
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
        meta: {
          permissions: ['journal.view']
        },
      },
      {
        path: 'post',
        name: 'Post Journal',
        component: PostJournalPage,
        meta: {
          permissions: ['journal.bill_entry']
        },
      },
      {
        path: 'party-ledger',
        name: 'Party Ledger',
        component: PartyLedgerPage,
        meta: {
          permissions: ['ledger.party']
        },
      },
      {
        path: 'general-ledger',
        name: 'General Ledger',
        component: GeneralLedgerPage,
        meta: {
          permissions: ['ledger.general']
        },
      },
      {
        path: ':id',
        name: 'Journal View',
        component: JournalViewPage,
        meta: {
          permissions: ['journal.view']
        },
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
        meta: {
          permissions: ['transaction_type.view']
        },
      },
    ],
  },
]
