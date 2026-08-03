export const accountTypeOptions = [
  { value: 'Cash', label: 'Cash' },
  { value: 'Bank', label: 'Bank' },
]

export const accountStatusOptions = [
  { value: 'Active', label: 'Active' },
  { value: 'Inactive', label: 'Inactive' },
]

export const accountStatusFilterOptions = [
  { value: 'all', label: 'All' },
  { value: 'Active', label: 'Active' },
  { value: 'Inactive', label: 'Inactive' },
]

export const initialAccounts = [
  {
    id: 1,
    account_name: 'Cash',
    account_label: 'Cash in Hand',
    account_type: 'Cash',
    icon: 'fa fa-money',
    current_balance: 125000,
    status: 'Active',
    created_at: '2026-05-01T10:00:00',
  },
  {
    id: 2,
    account_name: 'Dutch-Bangla Bank PLC',
    account_label: 'Motijheel Branch',
    account_type: 'Bank',
    bank_id: 1,
    icon: 'fa fa-university',
    current_balance: 320000,
    status: 'Active',
    created_at: '2026-05-01T10:15:00',
  },
]
