export const INCOME_CATEGORY_CODES = {
  RECRUITMENT_INCOME: 'recruitment_income',
  CLIENT_INCOME: 'client_income',
  OTHER_INCOME: 'other_income',
}

export const RECRUITMENT_INCOME_CATEGORY_CODE = INCOME_CATEGORY_CODES.RECRUITMENT_INCOME
export const CLIENT_INCOME_CATEGORY_CODE = INCOME_CATEGORY_CODES.CLIENT_INCOME
export const OTHER_INCOME_CATEGORY_CODE = INCOME_CATEGORY_CODES.OTHER_INCOME

export function findCategoryByCode(categories, code) {
  if (!code) return null

  const byCode = categories.find((category) => category.code === code)
  if (byCode) return byCode

  // Fallback when legacy categories were created without codes.
  const nameByCode = {
    [INCOME_CATEGORY_CODES.RECRUITMENT_INCOME]: 'recruitment income',
    [INCOME_CATEGORY_CODES.CLIENT_INCOME]: 'client income',
    [INCOME_CATEGORY_CODES.OTHER_INCOME]: 'operating income',
  }

  const expectedName = nameByCode[code]
  if (!expectedName) return null

  return (
    categories.find(
      (category) => String(category.name || '').trim().toLowerCase() === expectedName
    ) ??
    // Legacy name before rename to Operating Income
    (code === INCOME_CATEGORY_CODES.OTHER_INCOME
      ? categories.find(
          (category) => String(category.name || '').trim().toLowerCase() === 'other income'
        )
      : null) ??
    null
  )
}

export function isCategoryCode(category, code) {
  if (!category || !code) return false
  if (category.code === code) return true

  const matched = findCategoryByCode([category], code)
  return Boolean(matched)
}
