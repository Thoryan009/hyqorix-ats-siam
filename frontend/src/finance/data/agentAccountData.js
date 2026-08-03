export const agentAccounts = [
  {
    id: 1,
    agent_id: 1,
    agent_code: 'AGT001',
    agent_name: 'Al Falah Recruitment',
    phone: '+880 1712345678',
    balance: 125000,
    opening_balance: 100000,
    bill_agent_id: 1,
    status: 'Active',
  },
  {
    id: 2,
    agent_id: 2,
    agent_code: 'AGT002',
    agent_name: 'Rahman Overseas Services',
    phone: '+880 1822334455',
    balance: 87500,
    opening_balance: 80000,
    bill_agent_id: 2,
    status: 'Active',
  },
  {
    id: 3,
    agent_id: 3,
    agent_code: 'AGT003',
    agent_name: 'Global Manpower Solutions',
    phone: '+880 1933445566',
    balance: 342000,
    opening_balance: 300000,
    bill_agent_id: 3,
    status: 'Active',
  },
  {
    id: 4,
    agent_id: 4,
    agent_code: 'AGT004',
    agent_name: 'Prime HR Consultancy',
    phone: '01644556677',
    balance: 56000,
    opening_balance: 50000,
    bill_agent_id: null,
    status: 'Active',
  },
  {
    id: 5,
    agent_id: 5,
    agent_code: 'AGT005',
    agent_name: 'Elite Placement House',
    phone: '01555667788',
    balance: 198750,
    opening_balance: 150000,
    bill_agent_id: null,
    status: 'Active',
  },
]

export function getBillableAgents(accounts = agentAccounts) {
  return accounts
    .filter((account) => account.bill_agent_id != null && account.status !== 'Inactive')
    .map((account) => ({
      id: account.bill_agent_id,
      agent_code: account.agent_code,
      agent_name: account.agent_name,
      phone: account.phone,
      email: account.email || '',
      country: account.country || 'Bangladesh',
    }))
}

export function normalizeAgentAccount(account) {
  return {
    ...account,
    agent_id:
      account.agent_id ??
      account.bill_agent_id ??
      null,
    status: account.status ?? 'Active',
  }
}

export function loadAgentAccounts(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(agentAccounts)
  }

  return saved.map((account) => normalizeAgentAccount(account))
}

/** @deprecated Use loadAgentAccounts instead */
export function hydrateAgentAccounts(saved) {
  return loadAgentAccounts(saved)
}
