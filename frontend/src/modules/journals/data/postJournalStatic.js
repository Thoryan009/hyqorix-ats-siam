export const transactionTypeOptions = [
  { id: 'journal_voucher', name: 'Journal Voucher' },
  { id: 'direct_expense', name: 'Direct Expense' },
  { id: 'operating_expense', name: 'Operating Expense' },
  { id: 'recruitment_revenue', name: 'Recruitment Revenue' },
  { id: 'recruitment_refund', name: 'Recruitment Refund' },
  { id: 'asset_purchase', name: 'Asset Purchase' },
  { id: 'asset_return', name: 'Asset Return' },
  { id: 'staff_advance', name: 'Staff Advance' },
  { id: 'advance_adjustment', name: 'Advance Adjustment' },
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
  { id: 'direct_cost', name: 'Direct Cost' },
  { id: 'operating_expense', name: 'Operating Expense' },
  { id: 'recruitment_revenue', name: 'Recruitment Revenue' },
  { id: 'sales_return_refund', name: 'Sales Return / Refund' },
  { id: 'asset', name: 'Asset' },
  { id: 'liability', name: 'Liability' },
]

export const todayIsoDate = () => {
  const date = new Date()
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export const defaultJournalForm = {
  voucher_no: '—',
  status: 'Draft',
  voucher_date: todayIsoDate(),
  transaction_type: '',
  reference_no: '',
  party_type: '',
  party_id: '',
  project_id: '',
  narration: '',
}

export const createEmptyJournalLine = (id = Date.now()) => ({
  id,
  account_id: '',
  sub_ledger: '',
  cost_type: '',
  debit: '',
  credit: '',
})

export const defaultJournalLines = [createEmptyJournalLine(1), createEmptyJournalLine(2)]
