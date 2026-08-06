export const accountTransactionCategoryOptions = [
  { id: 'main', name: 'Main Account' },
  { id: 'staff', name: 'Staff Account' },
  { id: 'agent', name: 'Agent Account' },
  { id: 'vendor', name: 'Vendor Account' },
  { id: 'principal', name: 'Principal Account' },
  { id: 'client', name: 'Client Account' },
  { id: 'applicant', name: 'Applicant Account' },
  { id: 'banks', name: 'Bank Account' },
  { id: 'owners', name: 'Owner Account' },
]

export const mainAccountTypeOptions = [
  { id: 'Cash', name: 'Cash' },
  { id: 'Bank', name: 'Bank' },
]

export const accountTransactionTypes = [
  { id: 'loan', label: 'Loan', icon: 'fa fa-handshake-o' },
  { id: 'advanced', label: 'Advanced', icon: 'fa fa-credit-card' },
  // Temporarily hidden from Make Payment
  // { id: 'loan_repay', label: 'Loan Repay', icon: 'fa fa-reply' },
  // { id: 'advanced_repay', label: 'Advanced Repay', icon: 'fa fa-undo' },
  // { id: 'adjust_minus', label: 'Adjust -', icon: 'fa fa-minus-circle' },
  // { id: 'adjust_plus', label: 'Adjust +', icon: 'fa fa-plus-circle' },
]

/** Includes system-generated types that appear in the Transactions list. */
export const accountTransactionDisplayTypes = [
  ...accountTransactionTypes,
  { id: 'loan_repay', label: 'Loan Repay', icon: 'fa fa-reply' },
  { id: 'advanced_repay', label: 'Advanced Repay', icon: 'fa fa-undo' },
  { id: 'adjust_minus', label: 'Adjust -', icon: 'fa fa-minus-circle' },
  { id: 'adjust_plus', label: 'Adjust +', icon: 'fa fa-plus-circle' },
  { id: 'bill_payment', label: 'Bill Payment', icon: 'fa fa-file-text-o' },
  { id: 'receive_payment', label: 'Receive Payment', icon: 'fa fa-money' },
  { id: 'opening_balance', label: 'Opening Balance', icon: 'fa fa-balance-scale' },
  { id: 'deposit', label: 'Deposit', icon: 'fa fa-arrow-down' },
  { id: 'withdraw', label: 'Withdraw', icon: 'fa fa-arrow-up' },
  { id: 'transfer', label: 'Transfer', icon: 'fa fa-exchange' },
]

const partyCategories = [
  'staff',
  'agent',
  'vendor',
  'principal',
  'client',
  'applicant',
  'banks',
  'owners',
]

export function isPartyAccountCategory(category) {
  return partyCategories.includes(category)
}

export function isAdjustmentTransactionType(type) {
  return type === 'adjust_minus' || type === 'adjust_plus'
}

export function getTransactionTypeLabel(type) {
  return accountTransactionDisplayTypes.find((item) => item.id === type)?.label ?? type
}

export function getAccountCategoryLabel(category) {
  return accountTransactionCategoryOptions.find((item) => item.id === category)?.name ?? category
}

export function getDefaultFromCategory(transactionType) {
  if (transactionType === 'loan') return 'main'
  if (transactionType === 'advanced') return 'agent'
  if (transactionType === 'loan_repay') return 'staff'
  if (transactionType === 'advanced_repay') return 'agent'
  return 'main'
}

export function getDefaultToCategory(transactionType) {
  if (transactionType === 'loan') return 'staff'
  if (transactionType === 'advanced') return 'main'
  if (transactionType === 'loan_repay' || transactionType === 'advanced_repay') return 'main'
  return 'staff'
}

export function getTransactionTypeHint(transactionType) {
  const hints = {
    loan: 'Debit the payment account and credit the receiving account.',
    advanced:
      'From Agent to Main: cash increases and agent ledger posts CR (liability to agent).',
    loan_repay: 'Debit the repaying account and credit the receiving main account.',
    advanced_repay:
      'From Agent to Main: agent ledger posts DR to clear the liability and cash decreases.',
    adjust_minus: 'Decrease the selected account balance without a counter account.',
    adjust_plus: 'Increase the selected account balance without a counter account.',
  }

  return hints[transactionType] ?? ''
}

export function normalizeAccountTransaction(entry) {
  return {
    ...entry,
    amount: Number(entry.amount) || 0,
    date: entry.date ?? entry.created_at?.slice(0, 10) ?? '',
  }
}
