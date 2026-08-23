export const transactionTypeOptions = [
  { id: 'journal_voucher', name: 'Journal Voucher' },
  { id: 'payment_voucher', name: 'Payment Voucher' },
  { id: 'receipt_voucher', name: 'Receipt Voucher' },
]

export const partyTypeOptions = [
  { id: '', name: 'None' },
  { id: 'Client', name: 'Client' },
  { id: 'Principal', name: 'Principal' },
  { id: 'Agent', name: 'Agent' },
  { id: 'Candidate', name: 'Candidate' },
  { id: 'Vendor', name: 'Vendor' },
  { id: 'Staff', name: 'Staff' },
]

export const projectOptions = [
  { id: 'general', name: 'General / No Project' },
  { id: 'CL001', name: 'CL001 – Client Project' },
  { id: 'demand_01', name: 'Demand – Manpower Batch A' },
]

export const costTypeOptions = [
  { id: 'general', name: 'General' },
  { id: 'direct', name: 'Direct Cost' },
  { id: 'operating', name: 'Operating Expense' },
]

export const defaultJournalForm = {
  voucher_no: '—',
  status: 'Draft',
  voucher_date: '',
  transaction_type: '',
  reference_no: '',
  party_type: '',
  party_id: '',
  project_id: '',
  narration: '',
}

export const defaultJournalLines = [
  {
    id: 1,
    account_id: '',
    sub_ledger: '',
    cost_type: '',
    debit: '',
    credit: '',
  },
  {
    id: 2,
    account_id: '',
    sub_ledger: '',
    cost_type: '',
    debit: '',
    credit: '',
  },
]
