export function getReceivableRemainingAmount(entry = {}) {
  if (entry.receivable_remaining != null && entry.receivable_remaining !== '') {
    return Math.max(Number(entry.receivable_remaining) || 0, 0)
  }

  const total = Number(entry.sale_price ?? entry.amount) || 0
  const paid = Number(entry.collected_amount ?? entry.paid_amount) || 0

  return Math.max(Math.round((total - paid) * 100) / 100, 0)
}

export function isOutstandingReceivable(entry = {}) {
  return (
    String(entry.payment_method || '').toLowerCase() === 'due' &&
    getReceivableRemainingAmount(entry) > 0
  )
}

export function formatPayerTypeLabel(payerType) {
  const value = String(payerType || '').toLowerCase()
  if (value === 'agent') return 'Agent'
  if (value === 'candidate') return 'Candidate'
  if (value === 'client') return 'Client'
  if (value === 'recruitment_income') return 'Recruitment Income'
  if (value === 'client_income') return 'Client Income'
  if (value === 'other_income') return 'Operating Income'
  if (value === 'income') return 'Income Account'
  return payerType || '—'
}

export function formatReceivableSourceLabel(entry = {}) {
  if (entry?.source !== 'pl_income') return 'Sale'

  const headName = String(entry?.income_head_name || '').trim()
  if (headName) return headName

  const categoryName = String(entry?.income_category_name || '').trim()
  if (categoryName) return categoryName

  return 'PL Income'
}

export function isSimplePlIncomeReceivable(entry = {}) {
  return (
    entry?.source === 'pl_income' &&
    entry?.receivable_kind === 'income_collection' &&
    Number(entry?.collection_id) > 0
  )
}
