const ACCOUNT_TYPE_KEYS = {
  Asset: 'accounts.account_type_asset',
  'Contra Asset': 'accounts.account_type_contra_asset',
  Liability: 'accounts.account_type_liability',
  Equity: 'accounts.account_type_equity',
  'Contra Equity': 'accounts.account_type_contra_equity',
  Revenue: 'accounts.account_type_revenue',
  'Contra Revenue': 'accounts.account_type_contra_revenue',
  'Other Operating Revenue': 'accounts.account_type_other_operating_revenue',
  'Other Income': 'accounts.account_type_other_income',
  'Direct Cost A': 'accounts.account_type_direct_cost_a',
  'Direct Cost B': 'accounts.account_type_direct_cost_b',
  'Operating Expense': 'accounts.account_type_operating_expense',
  'Finance Cost': 'accounts.account_type_finance_cost',
  'Other Expense': 'accounts.account_type_other_expense',
}

export function localizeAccountType(type, t, fallback = '') {
  const key = ACCOUNT_TYPE_KEYS[type]
  if (key) return t(key)
  return fallback || type || ''
}
