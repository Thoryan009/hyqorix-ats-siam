export const EXPENSE_CATEGORY_CODES = {
  DIRECT_COST: 'direct_cost',
  CLIENT_RECRUITMENT_COST: 'client_recruitment_cost',
  OPERATING_COST: 'operating_cost',
}

export const DIRECT_COST_CATEGORY_CODE = EXPENSE_CATEGORY_CODES.DIRECT_COST
export const CLIENT_RECRUITMENT_COST_CATEGORY_CODE = EXPENSE_CATEGORY_CODES.CLIENT_RECRUITMENT_COST
export const OPERATING_COST_CATEGORY_CODE = EXPENSE_CATEGORY_CODES.OPERATING_COST

export function findCategoryByCode(categories, code) {
  if (!code) return null

  const byCode = categories.find((category) => category.code === code)
  if (byCode) return byCode

  // Fallback when legacy categories were created without codes.
  const nameByCode = {
    [EXPENSE_CATEGORY_CODES.DIRECT_COST]: 'direct expense',
    [EXPENSE_CATEGORY_CODES.CLIENT_RECRUITMENT_COST]: 'client recruitment expense',
    [EXPENSE_CATEGORY_CODES.OPERATING_COST]: 'operating expense',
  }

  const expectedName = nameByCode[code]
  if (!expectedName) return null

  return (
    categories.find(
      (category) => String(category.name || '').trim().toLowerCase() === expectedName
    ) ?? null
  )
}

export function isCategoryCode(category, code) {
  if (!category || !code) return false
  return category.code === code
}
