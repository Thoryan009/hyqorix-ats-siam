import { accountTransactionCategoryOptions } from './accountTransactionData'
import { INCOME_TYPE_TO_ACCOUNT_CATEGORY } from './accountCategoryCodes'
import { incomeAccountConfigs } from '../config/incomeAccountConfigs'

export const incomeAccountCategoryOptions = Object.entries(incomeAccountConfigs).map(
  ([id, config]) => ({
    id,
    name: `${config.incomeTypeLabel} Account`,
  }),
)

export const incomeHeadAccountCategoryOptions = [
  ...accountTransactionCategoryOptions,
  ...incomeAccountCategoryOptions,
]

export function getIncomeTypeFromAccountCategory(category) {
  if (incomeAccountConfigs[category]) {
    return category
  }

  const entry = Object.entries(INCOME_TYPE_TO_ACCOUNT_CATEGORY).find(
    ([, accountCategory]) => accountCategory === category
  )

  return entry?.[0] ?? null
}

export function isIncomeAccountCategory(category) {
  return Boolean(getIncomeTypeFromAccountCategory(category))
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
    incomeHeadAccountCategoryOptions.find((option) => option.id === category)?.name ?? category
  )
}

export function getAvailableAccountCategoryOptions(selectedCategories = [], currentCategory = '') {
  const selected = new Set(
    selectedCategories.filter((category) => category && category !== currentCategory),
  )

  return incomeHeadAccountCategoryOptions.filter((option) => !selected.has(option.id))
}
