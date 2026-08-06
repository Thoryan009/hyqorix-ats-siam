import AccountsPage from './pages/AccountsPage.vue'
import AccountLedgerPage from './pages/AccountLedgerPage.vue'
import AccountLedgerPrintPage from './pages/AccountLedgerPrintPage.vue'
import AgentPaymentPage from './pages/AgentPaymentPage.vue'
import AgentLedgerPage from './pages/AgentLedgerPage.vue'
import AgentLedgerPrintPage from './pages/AgentLedgerPrintPage.vue'
import PartyLedgerPage from './pages/PartyLedgerPage.vue'
import PartyLedgerPrintPage from './pages/PartyLedgerPrintPage.vue'
import AgentBillListPage from './pages/AgentBillListPage.vue'
import GenerateAgentBillPage from './pages/GenerateAgentBillPage.vue'
import ExpenseManagementPage from './pages/ExpenseManagementPage.vue'
import IncomeManagementPage from './pages/IncomeManagementPage.vue'
import IncomeListPage from './pages/IncomeListPage.vue'
import PaymentReceivedPage from './pages/PaymentReceivedPage.vue'
import PaymentCollectionPage from './pages/PaymentCollectionPage.vue'
import ExpenseEntriesPage from './pages/ExpenseEntriesPage.vue'
import BillGenerationPage from './pages/BillGenerationPage.vue'
import SubmittedBillsPage from './pages/SubmittedBillsPage.vue'
import RejectedBillsPage from './pages/RejectedBillsPage.vue'
import BillApprovePage from './pages/BillApprovePage.vue'
import BillReceivableReceivePage from './pages/BillReceivableReceivePage.vue'
import AccountTransactionsPage from './pages/AccountTransactionsPage.vue'
import ExpenseCostLedgerPage from './pages/ExpenseCostLedgerPage.vue'
import GrossProfitReportPage from './pages/GrossProfitReportPage.vue'
import TrialBalancePage from './pages/TrialBalancePage.vue'
import IncomeStatementPage from './pages/IncomeStatementPage.vue'
import BalanceSheetPage from './pages/BalanceSheetPage.vue'
import DashboardLayout from '@/shared/layouts/DashboardLayout.vue'

function createPartyLedgerRoutes(partyType, tabId, ledgerName, printName) {
  const basePath = `/finance/${tabId}`

  return [
    {
      path: basePath,
      component: DashboardLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: (to) => ({
            path: '/finance/accounts',
            query: { ...to.query, tab: tabId },
          }),
        },
        {
          path: ':accountId/ledger',
          name: ledgerName,
          component: PartyLedgerPage,
          props: { partyType },
          meta: {
            permissions: ['finance_account.view', 'transaction.view'],
          },
        },
      ],
    },
    {
      path: `${basePath}/:accountId/ledger/print`,
      name: printName,
      component: PartyLedgerPrintPage,
      props: { partyType },
      meta: { requiresAuth: true, permissions: ['finance_account.view', 'transaction.view'] },
    },
  ]
}

export default [
  {
    path: '/finance/accounts',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Accounts',
        component: AccountsPage,
        meta: {
          permissions: ['finance_account.view'],
        },
      },
      {
        path: ':accountId/ledger',
        name: 'Account Ledger',
        component: AccountLedgerPage,
        meta: {
          permissions: ['finance_account.view'],
        },
      },
    ],
  },
  {
    path: '/finance/expense-cost-accounts',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: ':costType/:accountId/ledger',
        name: 'Expense Cost Ledger',
        component: ExpenseCostLedgerPage,
        meta: {
          permissions: ['finance_account.view'],
        },
      },
    ],
  },
  {
    path: '/finance/accounts/:accountId/ledger/print',
    name: 'Account Ledger Print',
    component: AccountLedgerPrintPage,
    meta: { requiresAuth: true, permissions: ['finance_account.view'] },
  },
  {
    path: '/finance/agent-accounts',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: (to) => ({
          path: '/finance/accounts',
          query: { ...to.query, tab: 'agent-accounts' },
        }),
      },
      {
        path: ':agentId/payment',
        name: 'Agent Payment',
        component: AgentPaymentPage,
        meta: {
          permissions: ['transaction.view'],
        },
      },
      {
        path: ':agentId/ledger',
        name: 'Agent Ledger',
        component: AgentLedgerPage,
        meta: {
          permissions: ['transaction.view'],
        },
      },
    ],
  },
  {
    path: '/finance/agent-accounts/:agentId/ledger/print',
    name: 'Agent Ledger Print',
    component: AgentLedgerPrintPage,
    meta: { requiresAuth: true, permissions: ['transaction.view'] },
  },
  ...createPartyLedgerRoutes('vendor', 'vendor-accounts', 'Vendor Ledger', 'Vendor Ledger Print'),
  ...createPartyLedgerRoutes('staff', 'staff-accounts', 'Staff Ledger', 'Staff Ledger Print'),
  ...createPartyLedgerRoutes('client', 'client-accounts', 'Client Ledger', 'Client Ledger Print'),
  ...createPartyLedgerRoutes(
    'applicant',
    'applicant-accounts',
    'Applicant Ledger',
    'Applicant Ledger Print',
  ),
  ...createPartyLedgerRoutes(
    'principal',
    'principal-accounts',
    'Principal Ledger',
    'Principal Ledger Print',
  ),
  ...createPartyLedgerRoutes('banks', 'bank-accounts', 'Bank Ledger', 'Bank Ledger Print'),
  ...createPartyLedgerRoutes('owners', 'owner-accounts', 'Owner Ledger', 'Owner Ledger Print'),
  // Legacy vendor print path (vendorId param)

  {
    path: '/finance/vendor-accounts/:vendorId/ledger/print',
    name: 'Vendor Ledger Print Legacy',
    component: PartyLedgerPrintPage,
    props: { partyType: 'vendor' },
    meta: { requiresAuth: true, permissions: ['finance_account.view'] },
  },
  {
    path: '/finance/expense-management',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Expense Setup',
        component: ExpenseManagementPage,
        meta: {
          permissions: ['account_setup.view'],
        },
      },
    ],
  },
  {
    path: '/finance/income-management',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Income Setup',
        component: IncomeManagementPage,
        meta: {
          permissions: ['account_setup.view'],
        },
      },
    ],
  },
  {
    path: '/finance/income-list',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Income List',
        component: IncomeListPage,
        meta: {
          permissions: ['income_list.view'],
        },
      },
    ],
  },
  // hello
  {
    path: '/finance/payment-received',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Payment / Received',
        component: PaymentReceivedPage,
        meta: {
          permissions: ['receive_payment.create'],
        },
      },
    ],
  },
  {
    path: '/finance/payment-collection',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Receipt List',
        component: PaymentCollectionPage,
        meta: {
          permissions: ['receive_list.view'],
        },
      },
    ],
  },
  {
    path: '/finance/expense-entries',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Paid Bills',
        component: ExpenseEntriesPage,
        meta: {
          permissions: ['paid_bill.view'],
        },
      },
    ],
  },
  {
    path: '/finance/bill-generation',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Bill Generation',
        component: BillGenerationPage,
        meta: {
          permissions: ['bill_generation.create'],
        },
      },
    ],
  },
  {
    path: '/finance/submitted-bills',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Submitted Bills',
        component: SubmittedBillsPage,
        meta: {
          permissions: ['submitted_bills.view'],
        },
      },
    ],
  },
  {
    path: '/finance/rejected-bills',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Rejected Bills',
        component: RejectedBillsPage,
        meta: {
          permissions: ['rejected_bills.view'],
        },
      },
    ],
  },
  {
    path: '/finance/bills-to-pay/:id',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Bill Payment Review',
        component: BillApprovePage,
        meta: {
          permissions: ['receive_payment.create'],
        },
      },
    ],
  },
  {
    path: '/finance/bills-payable/:id',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Bill Payable Payment',
        component: BillApprovePage,
        meta: {
          permissions: ['receive_payment.create'],
        },
      },
    ],
  },
  {
    path: '/finance/bills-receivable/:id',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Bill Receivable Receive',
        component: BillReceivableReceivePage,
        meta: {
          permissions: ['receive_payment.create'],
        },
      },
    ],
  },
  {
    path: '/finance/transactions',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Finance Transactions',
        component: AccountTransactionsPage,
        meta: {
          permissions: ['transaction.view'],
        },
      },
    ],
  },
  {
    path: '/finance/reports/gross-profit',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Gross Profit Report',
        component: GrossProfitReportPage,
        meta: {
          permissions: ['gross_profit_report.view'],
        },
      },
    ],
  },
  {
    path: '/finance/reports/trial-balance',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Final Trial Balance',
        component: TrialBalancePage,
        meta: {
          permissions: ['trial_balance.view'],
        },
      },
    ],
  },
  {
    path: '/finance/reports/income-statement',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Income Statement',
        component: IncomeStatementPage,
        meta: {
          permissions: ['income_statement.view'],
        },
      },
    ],
  },
  {
    path: '/finance/reports/balance-sheet',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Balance Sheet',
        component: BalanceSheetPage,
        meta: {
          permissions: ['balance_sheet.view'],
        },
      },
    ],
  },
  // {
  //   path: '/finance/agent-bills',
  //   component: DashboardLayout,
  //   meta: { requiresAuth: true },
  //   children: [
  //     {
  //       path: '',
  //       name: 'Agent Bill List',
  //       component: AgentBillListPage,
  //       meta: {
  //         permissions: ['transaction.view'],
  //       },
  //     },
  //     {
  //       path: 'generate',
  //       name: 'Generate Agent Bill',
  //       component: GenerateAgentBillPage,
  //       meta: {
  //         permissions: ['transaction.view'],
  //       },
  //     },
  //   ],
  // },
]
