import { getExpenseCostConfig } from '../config/expenseCostAccountConfigs'
import { getIncomeAccountConfig } from '../config/incomeAccountConfigs'
import { getPartyConfig } from '../config/partyAccountConfigs'
import {
  getAccountCategoryLabel,
  isExpenseCostAccountCategory,
  normalizeLinkedAccounts,
} from '../data/expenseHeadAccountLinkData'
import { isIncomeAccountCategory } from '../data/incomeHeadAccountLinkData'

function mapExpenseCostAccount(account, formatCurrency) {
  return {
    id: account.id,
    name: `${account.head_name} — ${formatCurrency(account.balance)}`,
    balance: Number(account.balance) || 0,
    accountType: 'Expense',
  }
}

function mapIncomeAccount(account, formatCurrency) {
  return {
    id: account.id,
    name: `${account.head_name} — ${formatCurrency(account.balance)}`,
    balance: Number(account.balance) || 0,
    accountType: 'Income',
  }
}

function mapMainAccount(account, formatCurrency) {
  return {
    id: account.id,
    name: `${account.account_type} — ${account.account_name} (${account.account_label}) — ${formatCurrency(account.current_balance)}`,
    balance: Number(account.current_balance) || 0,
    accountType: account.account_type,
  }
}

function mapAgentAccount(account, formatCurrency) {
  return {
    id: account.id,
    name: `Agent — ${account.agent_code} — ${account.agent_name} — ${formatCurrency(account.balance)}`,
    balance: Number(account.balance) || 0,
    accountType: 'Agent',
  }
}

function mapPartyAccount(account, partyType, formatCurrency) {
  const config = getPartyConfig(partyType)
  if (!config) return null

  return {
    id: account.id,
    name: `${config.partyLabel} — ${account[config.codeKey]} — ${account[config.nameKey]} — ${formatCurrency(account.balance)}`,
    balance: Number(account.balance) || 0,
    accountType: config.partyLabel,
  }
}

export function getAccountsForLinkedCategory(category, headId, stores, formatCurrency, head = null) {
  if (!category) return []

  if (isExpenseCostAccountCategory(category)) {
    const account = stores.expenseCostAccountsStore.getAccountByHeadId(head?.category_id, headId)
    if (!account || account.status !== 'Active') return []

    return [mapExpenseCostAccount(account, formatCurrency)]
  }

  if (isIncomeAccountCategory(category)) {
    const account = stores.incomeAccountsStore.getAccountByHeadId(head?.category_id, headId)
    if (!account || account.status !== 'Active') return []

    return [mapIncomeAccount(account, formatCurrency)]
  }

  if (category === 'main') {
    return stores.accountStore
      .getActiveAccounts()
      .map((account) => mapMainAccount(account, formatCurrency))
  }

  if (category === 'agent') {
    return stores.agentAccountStore.accounts
      .filter((account) => account.status === 'Active')
      .map((account) => mapAgentAccount(account, formatCurrency))
  }

  const config = getPartyConfig(category)
  if (!config) return []

  return stores.partyAccountsStore
    .getAccounts(category)
    .filter((account) => account.status === 'Active')
    .map((account) => mapPartyAccount(account, category, formatCurrency))
    .filter(Boolean)
}

export function buildLinkedAccountGroups(head, stores, formatCurrency) {
  const links = normalizeLinkedAccounts(head?.linked_accounts)

  return links.map((link) => {
    const accounts = getAccountsForLinkedCategory(
      link.account_category,
      head?.id,
      stores,
      formatCurrency,
      head
    )

    return {
      category: link.account_category,
      label: getAccountCategoryLabel(link.account_category),
      accounts,
    }
  })
}

export function findLinkedAccountSelection(groups, category, accountId) {
  if (!category || !accountId) return null

  const group = groups.find((item) => item.category === category)
  if (!group) return null

  const account = group.accounts.find((item) => Number(item.id) === Number(accountId))
  if (!account) return null

  return {
    category,
    categoryLabel: group.label,
    account,
  }
}

export function getLinkedBillAccountTypeLabel(entry = {}) {
  if (entry.linked_account_category) {
    return getAccountCategoryLabel(entry.linked_account_category)
  }

  if (entry.expense_cost_type) {
    return getExpenseCostConfig(entry.expense_cost_type)?.costTypeLabel ?? 'Expense Cost Account'
  }

  if (entry.income_type) {
    return getIncomeAccountConfig(entry.income_type)?.incomeTypeLabel ?? 'Income Account'
  }

  return ''
}

export function getLinkedBillAccountName(entry = {}) {
  if (entry.linked_account_name) {
    return entry.linked_account_name
  }

  if (entry.expense_cost_account_name) {
    return entry.expense_cost_account_name
  }

  if (entry.income_account_name) {
    return entry.income_account_name
  }

  return ''
}

export function hasLinkedBillAccount(entry = {}) {
  if (entry.linked_account_category && entry.linked_account_id) {
    return true
  }

  return Boolean(getLinkedBillAccountTypeLabel(entry) && getLinkedBillAccountName(entry))
}

export function resolveLinkedBillAccountBalance(category, accountId, stores) {
  if (!category || !accountId) return null

  if (isExpenseCostAccountCategory(category)) {
    const account = stores.expenseCostAccountsStore.getAccount(category, accountId)
    return account ? Number(account.balance) : null
  }

  if (isIncomeAccountCategory(category)) {
    const account = stores.incomeAccountsStore.getAccount(category, accountId)
    return account ? Number(account.balance) : null
  }

  if (category === 'main') {
    const account = stores.accountStore.getAccount(accountId)
    return account ? Number(account.current_balance) : null
  }

  if (category === 'agent') {
    const account = stores.agentAccountStore.getAccount(accountId)
    return account ? Number(account.balance) : null
  }

  const account = stores.partyAccountsStore.getAccount(category, accountId)
  return account ? Number(account.balance) : null
}
