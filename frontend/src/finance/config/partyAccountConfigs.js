export const partyAccountConfigs = {
  agent: {
    tabId: 'agent-accounts',
    label: 'Agent Accounts',
    partyLabel: 'Agent',
    partyLabelPlural: 'Agents',
    accountCategory: 'agent',
    rowIcon: 'fa fa-user-secret',
    nameKey: 'agent_name',
    codeKey: 'agent_code',
    summaryTitle: 'Agent Balance Summary',
    searchPlaceholder: 'Search by agent name, code, or phone...',
    tableNameLabel: 'Agent',
    useAgentStore: true,
    actions: {
      ledger: true,
      payment: false,
      generateBill: false,
      create: true,
      edit: true,
    },
    quickActions: [
      { label: 'Job Wise Billing', icon: 'fa fa-list-alt', route: '/finance/agent-bills' },
      { label: 'Company Accounts', icon: 'fa fa-bank', route: '/finance/accounts' },
    ],
    ledgerPath: (accountId) => `/finance/agent-accounts/${accountId}/ledger`,
  },
  vendor: {
    tabId: 'vendor-accounts',
    label: 'Vendor Accounts',
    partyLabel: 'Vendor',
    partyLabelPlural: 'Vendors',
    accountCategory: 'vendor',
    rowIcon: 'fa fa-plane',
    nameKey: 'vendor_name',
    codeKey: 'vendor_code',
    idKey: 'vendor_id',
    summaryTitle: 'Vendor Amount Summary',
    searchPlaceholder: 'Search by vendor name, code, or phone...',
    tableNameLabel: 'Vendor',
    useAmountLabel: true,
    actions: {
      ledger: true,
      payment: false,
      generateBill: false,
      create: true,
      edit: true,
    },
    quickActions: [
      { label: 'Company Accounts', icon: 'fa fa-bank', route: '/finance/accounts' },
    ],
    ledgerPath: (accountId) => `/finance/vendor-accounts/${accountId}/ledger`,
  },
  principal: {
    tabId: 'principal-accounts',
    label: 'Principal Accounts',
    partyLabel: 'Principal',
    partyLabelPlural: 'Principals',
    accountCategory: 'principal',
    rowIcon: 'fa fa-user',
    nameKey: 'principal_name',
    codeKey: 'principal_code',
    idKey: 'principal_id',
    summaryTitle: 'Principal Balance Summary',
    searchPlaceholder: 'Search by principal name, code, or phone...',
    tableNameLabel: 'Principal',
    actions: {
      ledger: true,
      payment: false,
      generateBill: false,
      create: true,
      edit: true,
    },
    quickActions: [
      { label: 'Company Accounts', icon: 'fa fa-bank', route: '/finance/accounts' },
    ],
    ledgerPath: (accountId) => `/finance/principal-accounts/${accountId}/ledger`,
  },
  client: {
    tabId: 'client-accounts',
    label: 'Client Accounts',
    partyLabel: 'Client',
    partyLabelPlural: 'Clients',
    accountCategory: 'client',
    rowIcon: 'fa fa-building',
    nameKey: 'client_name',
    codeKey: 'client_code',
    idKey: 'client_id',
    summaryTitle: 'Client Balance Summary',
    searchPlaceholder: 'Search by client name, code, or phone...',
    tableNameLabel: 'Client',
    useAmountLabel: true,
    ledgerReferenceLabel: 'Candidate',
    actions: {
      ledger: true,
      payment: false,
      generateBill: false,
      create: true,
      edit: true,
    },
    quickActions: [
      { label: 'Client Bills', icon: 'fa fa-file-text-o', route: '/applications/client-bills' },
      { label: 'Company Accounts', icon: 'fa fa-bank', route: '/finance/accounts' },
    ],
    ledgerPath: (accountId) => `/finance/client-accounts/${accountId}/ledger`,
  },
  staff: {
    tabId: 'staff-accounts',
    label: 'Staff Accounts',
    partyLabel: 'Staff',
    partyLabelPlural: 'Staff',
    accountCategory: 'staff',
    rowIcon: 'fa fa-id-badge',
    nameKey: 'staff_name',
    codeKey: 'staff_code',
    idKey: 'staff_id',
    summaryTitle: 'Staff Balance Summary',
    searchPlaceholder: 'Search by staff name, code, or phone...',
    tableNameLabel: 'Staff',
    actions: {
      ledger: true,
      payment: false,
      generateBill: false,
      create: true,
      edit: true,
    },
    quickActions: [
      { label: 'Company Accounts', icon: 'fa fa-bank', route: '/finance/accounts' },
    ],
    ledgerPath: (accountId) => `/finance/staff-accounts/${accountId}/ledger`,
  },
  applicant: {
    tabId: 'applicant-accounts',
    label: 'Applicant Accounts',
    partyLabel: 'Applicant',
    partyLabelPlural: 'Applicants',
    accountCategory: 'applicant',
    rowIcon: 'fa fa-user-o',
    nameKey: 'applicant_name',
    codeKey: 'applicant_code',
    idKey: 'applicant_id',
    summaryTitle: 'Applicant Amount Summary',
    searchPlaceholder: 'Search by applicant name, application ID, passport, or phone...',
    tableNameLabel: 'Applicant',
    useAmountLabel: true,
    extraColumns: [{ key: 'passport_no', label: 'Passport No' }],
    actions: {
      ledger: true,
      payment: false,
      generateBill: false,
      create: false,
      edit: true,
    },
    quickActions: [
      { label: 'Company Accounts', icon: 'fa fa-bank', route: '/finance/accounts' },
    ],
    ledgerPath: (accountId) => `/finance/applicant-accounts/${accountId}/ledger`,
  },
}

export const partyTabIds = Object.values(partyAccountConfigs).map((config) => config.tabId)

export function getPartyConfig(partyType) {
  return partyAccountConfigs[partyType] ?? null
}

export function getPartyTypeFromTab(tabId) {
  const entry = Object.entries(partyAccountConfigs).find(([, config]) => config.tabId === tabId)
  return entry?.[0] ?? null
}

export const partyTypesWithAccounts = ['vendor', 'principal', 'client', 'staff', 'applicant']

export function isPartyAccountsType(partyType) {
  return partyTypesWithAccounts.includes(partyType)
}
