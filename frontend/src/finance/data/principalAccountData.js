export const principalAccounts = [
  {
    id: 1,
    principal_id: 1,
    principal_code: 'PRC001',
    principal_name: 'Al Noor Overseas',
    phone: '+880 1611223344',
    balance: 210000,
    opening_balance: 180000,
    status: 'Active',
  },
  {
    id: 2,
    principal_id: 2,
    principal_code: 'PRC002',
    principal_name: 'Gulf Star Manpower',
    phone: '+880 1722334455',
    balance: 156750,
    opening_balance: 140000,
    status: 'Active',
  },
  {
    id: 3,
    principal_id: 3,
    principal_code: 'PRC003',
    principal_name: 'Royal Workforce LLC',
    phone: '+880 1833445566',
    balance: -8200,
    opening_balance: 0,
    status: 'Active',
  },
]

export function normalizePrincipalAccount(account) {
  return {
    ...account,
    principal_id: account.principal_id ?? null,
    status: account.status ?? 'Active',
  }
}

export function loadPrincipalAccounts(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(principalAccounts)
  }

  return saved.map((account) => normalizePrincipalAccount(account))
}

/** @deprecated Use loadPrincipalAccounts instead */
export function hydratePrincipalAccounts(saved) {
  return loadPrincipalAccounts(saved)
}
