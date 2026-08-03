export const staffAccounts = [
  {
    id: 1,
    staff_id: 1,
    staff_code: 'STF001',
    staff_name: 'Md. Hasan Rahman',
    phone: '+880 1711111111',
    balance: 35000,
    opening_balance: 30000,
    status: 'Active',
  },
  {
    id: 2,
    staff_id: 2,
    staff_code: 'STF002',
    staff_name: 'Nusrat Jahan',
    phone: '+880 1811111111',
    balance: 17500,
    opening_balance: 15000,
    status: 'Active',
  },
  {
    id: 3,
    staff_id: 3,
    staff_code: 'STF003',
    staff_name: 'Tanvir Ahmed',
    phone: '+880 1911111111',
    balance: -5000,
    opening_balance: 0,
    status: 'Active',
  },
]

export function normalizeStaffAccount(account) {
  return {
    ...account,
    account_type: account.account_type ?? 'Staff',
    staff_id: account.staff_id ?? null,
    status: account.status ?? 'Active',
  }
}

export function loadStaffAccounts(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(staffAccounts)
  }

  return saved.map((account) => normalizeStaffAccount(account))
}
