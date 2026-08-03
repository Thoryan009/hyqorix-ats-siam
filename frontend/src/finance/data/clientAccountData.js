export const clientAccounts = [
  {
    id: 1,
    client_id: 1,
    client_code: 'CLT001',
    client_name: 'Bengal Trade International',
    phone: '+880 1719001122',
    balance: 325000,
    opening_balance: 300000,
    status: 'Active',
  },
  {
    id: 2,
    client_id: 2,
    client_code: 'CLT002',
    client_name: 'Metro Hiring Solutions',
    phone: '+880 1829002233',
    balance: 178400,
    opening_balance: 150000,
    status: 'Active',
  },
  {
    id: 3,
    client_id: 3,
    client_code: 'CLT003',
    client_name: 'Summit Overseas Ltd',
    phone: '+880 1939003344',
    balance: -15400,
    opening_balance: 0,
    status: 'Active',
  },
  {
    id: 4,
    client_id: 4,
    client_code: 'CLT004',
    client_name: 'Horizon Recruitment Co',
    phone: '+880 1649004455',
    balance: 92000,
    opening_balance: 85000,
    status: 'Active',
  },
]

export function normalizeClientAccount(account) {
  return {
    ...account,
    client_id: account.client_id ?? null,
    status: account.status ?? 'Active',
  }
}

export function loadClientAccounts(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(clientAccounts)
  }

  return saved.map((account) => normalizeClientAccount(account))
}

/** @deprecated Use loadClientAccounts instead */
export function hydrateClientAccounts(saved) {
  return loadClientAccounts(saved)
}
