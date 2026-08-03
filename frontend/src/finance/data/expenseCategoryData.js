export const initialExpenseCategories = [
  {
    id: 1,
    name: 'Direct Expense',
    description: 'Direct expenses related to recruitment operations',
    status: 'Active',
  },
  {
    id: 2,
    name: 'Client Recruitment Expense',
    description: 'Client-specific recruitment related expenses',
    status: 'Active',
  },
  {
    id: 3,
    name: 'Operating Expense',
    description: 'General operating and administrative expenses',
    status: 'Active',
  },
]

export function normalizeExpenseCategory(category) {
  return {
    ...category,
    description: category.description ?? '',
    status: category.status ?? 'Active',
  }
}

export function loadExpenseCategories(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(initialExpenseCategories)
  }

  return saved.map((category) => normalizeExpenseCategory(category))
}
