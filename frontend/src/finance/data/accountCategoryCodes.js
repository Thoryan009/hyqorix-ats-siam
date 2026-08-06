export const ACCOUNT_CATEGORIES = {
  MAIN: 'main',
  CAPITAL: 'capital',
  OWNERS_EQUITY: 'owners_equity',
  ASSET: 'asset',
  LIABILITIES: 'liabilities',
  AGENT_ADVANCED: 'agent_advanced',
  SALE: 'sale',
  BILLS_RECEIVABLE: 'bills_receivable',
  INCOME_RECEIVABLE: 'income_receivable',
  EXPENSE_PAYABLE: 'expense_payable',
  DIRECT_EXPENSE: 'direct_expense',
  CLIENT_RECRUITMENT: 'client_recruitment',
  OPERATING_EXPENSE: 'operating_expense',
  RECRUITMENT_INCOME: 'recruitment_income',
  CLIENT_INCOME: 'client_income',
  OTHER_INCOME: 'other_income',
  AGENT: 'agent',
  VENDOR: 'vendor',
  PRINCIPAL: 'principal',
  CLIENT: 'client',
  STAFF: 'staff',
  APPLICANT: 'applicant',
  BANKS: 'banks',
  OWNERS: 'owners',
}

export const ACCOUNT_CATEGORY_OPTIONS = [
  { id: ACCOUNT_CATEGORIES.MAIN, name: 'Main Account' },
  { id: ACCOUNT_CATEGORIES.OWNERS_EQUITY, name: "Owner's Equity Account" },
  { id: ACCOUNT_CATEGORIES.ASSET, name: 'Asset Account' },
  { id: ACCOUNT_CATEGORIES.LIABILITIES, name: 'Liabilities Account' },
  { id: ACCOUNT_CATEGORIES.DIRECT_EXPENSE, name: 'Direct Expense Account' },
  { id: ACCOUNT_CATEGORIES.CLIENT_RECRUITMENT, name: 'Client Recruitment Account' },
  { id: ACCOUNT_CATEGORIES.OPERATING_EXPENSE, name: 'Operating Expense Account' },
  { id: ACCOUNT_CATEGORIES.RECRUITMENT_INCOME, name: 'Recruitment Income Account' },
  { id: ACCOUNT_CATEGORIES.CLIENT_INCOME, name: 'Client Income Account' },
  { id: ACCOUNT_CATEGORIES.OTHER_INCOME, name: 'Operating Income Account' },
  { id: ACCOUNT_CATEGORIES.AGENT, name: 'Agent Account' },
  { id: ACCOUNT_CATEGORIES.VENDOR, name: 'Vendor Account' },
  { id: ACCOUNT_CATEGORIES.PRINCIPAL, name: 'Principal Account' },
  { id: ACCOUNT_CATEGORIES.CLIENT, name: 'Client Account' },
  { id: ACCOUNT_CATEGORIES.STAFF, name: 'Staff Account' },
  { id: ACCOUNT_CATEGORIES.APPLICANT, name: 'Applicant Account' },
  { id: ACCOUNT_CATEGORIES.BANKS, name: 'Bank Account' },
  { id: ACCOUNT_CATEGORIES.OWNERS, name: 'Owner Account' },
]

/** Legacy expense cost tab keys → unified account category */
export const COST_TYPE_TO_ACCOUNT_CATEGORY = {
  direct_cost: ACCOUNT_CATEGORIES.DIRECT_EXPENSE,
  client_recruitment_cost: ACCOUNT_CATEGORIES.CLIENT_RECRUITMENT,
  operating_cost: ACCOUNT_CATEGORIES.OPERATING_EXPENSE,
}

export const INCOME_TYPE_TO_ACCOUNT_CATEGORY = {
  recruitment_income: ACCOUNT_CATEGORIES.RECRUITMENT_INCOME,
  client_income: ACCOUNT_CATEGORIES.CLIENT_INCOME,
  other_income: ACCOUNT_CATEGORIES.OTHER_INCOME,
}

export const PARTY_TYPE_TO_ACCOUNT_CATEGORY = {
  agent: ACCOUNT_CATEGORIES.AGENT,
  vendor: ACCOUNT_CATEGORIES.VENDOR,
  principal: ACCOUNT_CATEGORIES.PRINCIPAL,
  client: ACCOUNT_CATEGORIES.CLIENT,
  staff: ACCOUNT_CATEGORIES.STAFF,
  applicant: ACCOUNT_CATEGORIES.APPLICANT,
  banks: ACCOUNT_CATEGORIES.BANKS,
  owners: ACCOUNT_CATEGORIES.OWNERS,
}

export function getAccountCategoryFromCostType(costType) {
  return COST_TYPE_TO_ACCOUNT_CATEGORY[costType] ?? null
}

export function getAccountCategoryFromIncomeType(incomeType) {
  return INCOME_TYPE_TO_ACCOUNT_CATEGORY[incomeType] ?? null
}

export function getAccountCategoryFromPartyType(partyType) {
  return PARTY_TYPE_TO_ACCOUNT_CATEGORY[partyType] ?? null
}
