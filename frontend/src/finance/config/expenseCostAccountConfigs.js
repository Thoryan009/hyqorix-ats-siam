import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'
import {
  CLIENT_RECRUITMENT_COST_CATEGORY_CODE,
  DIRECT_COST_CATEGORY_CODE,
  OPERATING_COST_CATEGORY_CODE,
} from '../data/expenseCategoryCodes'

export const expenseCostAccountConfigs = {
  direct_cost: {
    tabId: 'direct-cost-accounts',
    label: 'Direct Expense Accounts',
    costTypeLabel: 'Direct Expense',
    accountCategory: ACCOUNT_CATEGORIES.DIRECT_EXPENSE,
    categoryCode: DIRECT_COST_CATEGORY_CODE,
    rowIcon: 'fa fa-briefcase',
    summaryTitle: 'Direct Expense Amount Summary',
    searchPlaceholder: 'Search by expense head name...',
    tableNameLabel: 'Expense Head',
  },
  client_recruitment_cost: {
    tabId: 'client-recruitment-cost-accounts',
    label: 'Client Recruitment Expense Accounts',
    costTypeLabel: 'Client Recruitment Expense',
    accountCategory: ACCOUNT_CATEGORIES.CLIENT_RECRUITMENT,
    categoryCode: CLIENT_RECRUITMENT_COST_CATEGORY_CODE,
    rowIcon: 'fa fa-file-text-o',
    summaryTitle: 'Client Recruitment Expense Amount Summary',
    searchPlaceholder: 'Search by expense head name...',
    tableNameLabel: 'Expense Head',
  },
  operating_cost: {
    tabId: 'operating-cost-accounts',
    label: 'Operating Expense Accounts',
    costTypeLabel: 'Operating Expense',
    accountCategory: ACCOUNT_CATEGORIES.OPERATING_EXPENSE,
    categoryCode: OPERATING_COST_CATEGORY_CODE,
    rowIcon: 'fa fa-cogs',
    summaryTitle: 'Operating Expense Amount Summary',
    searchPlaceholder: 'Search by expense head name...',
    tableNameLabel: 'Expense Head',
  },
}

export const expenseCostTabIds = Object.values(expenseCostAccountConfigs).map(
  (config) => config.tabId
)

export function getExpenseCostConfig(costType) {
  return expenseCostAccountConfigs[costType] ?? null
}

export function getExpenseCostTypeFromTab(tabId) {
  const entry = Object.entries(expenseCostAccountConfigs).find(
    ([, config]) => config.tabId === tabId
  )
  return entry?.[0] ?? null
}

export function isExpenseCostAccountsType(costType) {
  return Boolean(expenseCostAccountConfigs[costType])
}

export function getCostTypeByCategoryCode(categoryCode) {
  const entry = Object.entries(expenseCostAccountConfigs).find(
    ([, config]) => config.categoryCode === categoryCode
  )
  return entry?.[0] ?? null
}

export function getCostTypeByCategory(category) {
  const code = typeof category === 'string' ? category : category?.code
  return getCostTypeByCategoryCode(code)
}

export function getCostTypeByCategoryId(categoryId, categories = []) {
  const category = categories.find((item) => Number(item.id) === Number(categoryId))
  return getCostTypeByCategory(category)
}

export function buildExpenseCostLedgerKey(costType, accountId) {
  return `${costType}:${Number(accountId)}`
}

export function parseExpenseCostLedgerKey(key) {
  const [costType, accountId] = String(key).split(':')
  return {
    costType,
    accountId: Number(accountId),
  }
}
