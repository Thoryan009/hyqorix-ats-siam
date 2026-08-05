import { ACCOUNT_CATEGORIES } from '../data/accountCategoryCodes'

export const companyManualAccountConfigs = {
  owners_equity: {
    tabId: 'owners-equity-accounts',
    label: "Owner's Equity",
    shortLabel: "Owner's Equity",
    typeLabel: "Owner's Equity",
    accountCategory: ACCOUNT_CATEGORIES.OWNERS_EQUITY,
    rowIcon: 'fa fa-pie-chart',
    searchPlaceholder: "Search owner's equity accounts...",
    createTitle: "Create Owner's Equity Account",
    namePlaceholder: 'Eg: Retained Earnings, Drawings',
  },
  asset: {
    tabId: 'asset-accounts',
    label: 'Asset Accounts',
    shortLabel: 'Asset',
    typeLabel: 'Asset',
    accountCategory: ACCOUNT_CATEGORIES.ASSET,
    rowIcon: 'fa fa-cubes',
    searchPlaceholder: 'Search asset accounts...',
    createTitle: 'Create Asset Account',
    namePlaceholder: 'Eg: Office Equipment, Vehicle',
  },
  liabilities: {
    tabId: 'liabilities-accounts',
    label: 'Liabilities Accounts',
    shortLabel: 'Liabilities',
    typeLabel: 'Liabilities',
    accountCategory: ACCOUNT_CATEGORIES.LIABILITIES,
    rowIcon: 'fa fa-balance-scale',
    searchPlaceholder: 'Search liabilities accounts...',
    createTitle: 'Create Liabilities Account',
    namePlaceholder: 'Eg: Bank Loan, Accounts Payable',
  },
}

export const companyManualAccountTabIds = Object.values(companyManualAccountConfigs).map(
  (config) => config.tabId
)

export function getCompanyManualAccountConfig(manualType) {
  return companyManualAccountConfigs[manualType] ?? null
}

export function getCompanyManualTypeFromTab(tabId) {
  const entry = Object.entries(companyManualAccountConfigs).find(
    ([, config]) => config.tabId === tabId
  )
  return entry?.[0] ?? null
}

export function isCompanyManualAccountsType(manualType) {
  return Boolean(companyManualAccountConfigs[manualType])
}
