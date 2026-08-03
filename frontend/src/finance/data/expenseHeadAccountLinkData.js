import { accountTransactionCategoryOptions } from './accountTransactionData'
import { COST_TYPE_TO_ACCOUNT_CATEGORY } from './accountCategoryCodes'
import { expenseCostAccountConfigs } from '../config/expenseCostAccountConfigs'

export const expenseCostAccountCategoryOptions = Object.entries(expenseCostAccountConfigs).map(
  ([id, config]) => ({
    id,
    name: `${config.costTypeLabel} Account`,
  }),
)

export const expenseHeadAccountCategoryOptions = [
  ...accountTransactionCategoryOptions,
  ...expenseCostAccountCategoryOptions,
]

export function getCostTypeFromAccountCategory(category) {
  if (expenseCostAccountConfigs[category]) {
    return category
  }

  const entry = Object.entries(COST_TYPE_TO_ACCOUNT_CATEGORY).find(
    ([, accountCategory]) => accountCategory === category
  )

  return entry?.[0] ?? null
}

export function isExpenseCostAccountCategory(category) {
  return Boolean(getCostTypeFromAccountCategory(category))
}

export function createEmptyLinkedAccount() {
  return {
    account_category: '',
  }
}

export function normalizeLinkedAccount(link = {}) {
  return {
    account_category: link.account_category ?? '',
  }
}

export function normalizeLinkedAccounts(links) {
  if (!Array.isArray(links)) return []
  return links.map((link) => normalizeLinkedAccount(link)).filter((link) => link.account_category)
}

export function getAccountCategoryLabel(category) {
  return (
    expenseHeadAccountCategoryOptions.find((option) => option.id === category)?.name ?? category
  )
}

export function getAvailableAccountCategoryOptions(selectedCategories = [], currentCategory = '') {
  const selected = new Set(
    selectedCategories.filter((category) => category && category !== currentCategory),
  )

  return expenseHeadAccountCategoryOptions.filter((option) => !selected.has(option.id))
}
