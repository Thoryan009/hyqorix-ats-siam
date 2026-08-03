export const initialDirectCostAccounts = []
export const initialClientRecruitmentCostAccounts = []
export const initialOperatingCostAccounts = []

export function normalizeExpenseCostAccount(account) {
  return {
    ...account,
    head_id: Number(account.head_id),
    category_id: Number(account.category_id),
    base_price: Number(account.base_price ?? 0),
    amount: Number(account.amount ?? 0),
    balance: Number(account.balance ?? 0),
    opening_balance: Number(account.opening_balance ?? account.balance ?? 0),
    status: account.status ?? 'Active',
  }
}

export function loadExpenseCostAccounts(saved, fallback = []) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(fallback)
  }

  return saved.map((account) => normalizeExpenseCostAccount(account))
}
