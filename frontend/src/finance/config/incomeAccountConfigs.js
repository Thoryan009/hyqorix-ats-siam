import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'
import {
  CLIENT_INCOME_CATEGORY_CODE,
  OTHER_INCOME_CATEGORY_CODE,
} from '../data/incomeCategoryCodes'

export const incomeAccountConfigs = {
  client_income: {
    tabId: 'client-income-accounts',
    label: 'Client Income Accounts',
    incomeTypeLabel: 'Client Income',
    accountCategory: ACCOUNT_CATEGORIES.CLIENT_INCOME,
    categoryCode: CLIENT_INCOME_CATEGORY_CODE,
    rowIcon: 'fa fa-building',
    summaryTitle: 'Client Income Amount Summary',
    searchPlaceholder: 'Search by income head name...',
    tableNameLabel: 'Income Head',
  },
    other_income: {
    tabId: 'other-income-accounts',
    label: 'Operating Income Accounts',
    incomeTypeLabel: 'Operating Income',
    accountCategory: ACCOUNT_CATEGORIES.OTHER_INCOME,
    categoryCode: OTHER_INCOME_CATEGORY_CODE,
    rowIcon: 'fa fa-plus-circle',
    summaryTitle: 'Operating Income Amount Summary',
    searchPlaceholder: 'Search by income head name...',
    tableNameLabel: 'Income Head',
  },
}

export const incomeAccountTabIds = Object.values(incomeAccountConfigs).map((config) => config.tabId)

export function getIncomeAccountConfig(incomeType) {
  return incomeAccountConfigs[incomeType] ?? null
}

export function getIncomeTypeFromTab(tabId) {
  const entry = Object.entries(incomeAccountConfigs).find(([, config]) => config.tabId === tabId)
  return entry?.[0] ?? null
}

export function isIncomeAccountsType(incomeType) {
  return Boolean(incomeAccountConfigs[incomeType])
}

export function getIncomeTypeByCategoryCode(categoryCode) {
  const entry = Object.entries(incomeAccountConfigs).find(
    ([, config]) => config.categoryCode === categoryCode
  )
  return entry?.[0] ?? null
}

export function getIncomeTypeByCategory(category) {
  const code = typeof category === 'string' ? category : category?.code
  return getIncomeTypeByCategoryCode(code)
}

export function getIncomeTypeByCategoryId(categoryId, categories = []) {
  const category = categories.find((item) => Number(item.id) === Number(categoryId))
  return getIncomeTypeByCategory(category)
}

export function buildIncomeAccountLedgerKey(incomeType, accountId) {
  return `${incomeType}:${Number(accountId)}`
}

export function parseIncomeAccountLedgerKey(key) {
  const [incomeType, accountId] = String(key).split(':')
  return {
    incomeType,
    accountId: Number(accountId),
  }
}
