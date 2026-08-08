const LEDGER_STORAGE_KEY = 'finance-agent-ledger-entries'
const ACCOUNTS_STORAGE_KEY = 'finance-agent-accounts'
const TRANSACTIONS_STORAGE_KEY = 'finance-agent-transactions'
const BILLS_STORAGE_KEY = 'finance-agent-bills'
const BANKS_STORAGE_KEY = 'finance-banks'
const PAYMENT_ACCOUNTS_STORAGE_KEY = 'finance-payment-accounts'
const ACCOUNT_LEDGER_STORAGE_KEY = 'finance-account-ledger-entries'
const ACCOUNT_TRANSACTIONS_STORAGE_KEY = 'finance-account-transactions'

export function loadFinanceJson(key, fallback) {
  try {
    const raw = localStorage.getItem(key)
    if (!raw) return structuredClone(fallback)

    const parsed = JSON.parse(raw)
    return parsed ?? structuredClone(fallback)
  } catch {
    return structuredClone(fallback)
  }
}

export function saveFinanceJson(key, value) {
  localStorage.setItem(key, JSON.stringify(value))
}

export function normalizeAgentEntriesMap(data) {
  const normalized = {}

  Object.entries(data || {}).forEach(([key, entries]) => {
    if (Array.isArray(entries)) {
      normalized[String(Number(key))] = entries
    }
  })

  return normalized
}

export const financeStorageKeys = {
  ledger: LEDGER_STORAGE_KEY,
  accounts: ACCOUNTS_STORAGE_KEY,
  transactions: TRANSACTIONS_STORAGE_KEY,
  bills: BILLS_STORAGE_KEY,
  banks: BANKS_STORAGE_KEY,
  paymentAccounts: PAYMENT_ACCOUNTS_STORAGE_KEY,
  accountLedger: ACCOUNT_LEDGER_STORAGE_KEY,
  accountTransactions: ACCOUNT_TRANSACTIONS_STORAGE_KEY,
  vendorAccounts: 'finance-vendor-accounts',
  principalAccounts: 'finance-principal-accounts',
  clientAccounts: 'finance-client-accounts',
  staffAccounts: 'finance-staff-accounts',
  agentMasterList: 'finance-agent-master-list',
  vendorMasterList: 'finance-vendor-master-list',
  principalMasterList: 'finance-principal-master-list',
  clientMasterList: 'finance-client-master-list',
  staffMasterList: 'finance-staff-master-list',
  expenseCategories: 'finance-expense-categories',
  expenseHeads: 'finance-expense-heads',
  expensePayments: 'finance-expense-payments',
  billEntries: 'finance-bill-entries',
  directCostAccounts: 'finance-direct-cost-accounts',
  clientRecruitmentCostAccounts: 'finance-client-recruitment-cost-accounts',
  operatingCostAccounts: 'finance-operating-cost-accounts',
  expenseCostLedger: 'finance-expense-cost-ledger',
  vendorLedger: 'finance-vendor-ledger-entries',
  partyLedger: 'finance-party-ledger-entries',
  accountTypeTransactions: 'finance-account-type-transactions',
}
