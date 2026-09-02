export const accountTypeOptions = [
  { id: 'Asset', name: 'Asset' },
  { id: 'Contra Asset', name: 'Contra Asset' },
  { id: 'Liability', name: 'Liability' },
  { id: 'Equity', name: 'Equity' },
  { id: 'Contra Equity', name: 'Contra Equity' },
  { id: 'Revenue', name: 'Revenue' },
  { id: 'Contra Revenue', name: 'Contra Revenue' },
  { id: 'Other Operating Revenue', name: 'Other Operating Revenue' },
  { id: 'Other Income', name: 'Other Income' },
  { id: 'Direct Cost A', name: 'Direct Cost A' },
  { id: 'Direct Cost B', name: 'Direct Cost B' },
  { id: 'Operating Expense', name: 'Operating Expense' },
  { id: 'Finance Cost', name: 'Finance Cost' },
  { id: 'Other Expense', name: 'Other Expense' },
]

export const financialStatementOptions = [
  { id: 'Balance Sheet', name: 'Balance Sheet' },
  { id: 'Gross Profit', name: 'Gross Profit' },
  { id: 'Income Statement', name: 'Income Statement' },
]

export const normalBalanceOptions = [
  { id: 'debit', name: 'Debit' },
  { id: 'credit', name: 'Credit' },
]

export const statusOptions = [
  { id: 'active', name: 'Active' },
  { id: 'inactive', name: 'Inactive' },
]

const financialStatementByType = {
  Asset: 'Balance Sheet',
  'Contra Asset': 'Balance Sheet',
  Liability: 'Balance Sheet',
  Equity: 'Balance Sheet',
  'Contra Equity': 'Balance Sheet',
  Revenue: 'Gross Profit',
  'Contra Revenue': 'Gross Profit',
  'Direct Cost A': 'Gross Profit',
  'Direct Cost B': 'Gross Profit',
  'Other Operating Revenue': 'Income Statement',
  'Other Income': 'Income Statement',
  'Operating Expense': 'Income Statement',
  'Finance Cost': 'Income Statement',
  'Other Expense': 'Income Statement',
}

const normalBalanceByType = {
  Asset: 'debit',
  'Contra Asset': 'credit',
  Liability: 'credit',
  Equity: 'credit',
  'Contra Equity': 'debit',
  Revenue: 'credit',
  'Contra Revenue': 'debit',
  'Direct Cost A': 'debit',
  'Direct Cost B': 'debit',
  'Other Operating Revenue': 'credit',
  'Other Income': 'credit',
  'Operating Expense': 'debit',
  'Finance Cost': 'debit',
  'Other Expense': 'debit',
}

export function financialStatementForType(type) {
  return financialStatementByType[type] ?? ''
}

export function normalBalanceForType(type) {
  return normalBalanceByType[type] ?? ''
}

export function applyTypeDefaults(formData = {}) {
  const type = formData.type
  const financialStatement = financialStatementForType(type)
  const normalBalance = normalBalanceForType(type)

  return {
    ...formData,
    ...(financialStatement ? { financial_statement: financialStatement } : {}),
    ...(normalBalance ? { normal_balance: normalBalance } : {}),
  }
}
