import { financeStorageKeys, loadFinanceJson } from '../utils/financeStorage'

export const initialExpensePayments = []

export const billEntryStatuses = [
  { id: 'submitted', name: 'Submitted' },
  { id: 'pending', name: 'Bills To Pay' },
  { id: 'approved', name: 'Approved' },
  { id: 'rejected', name: 'Rejected' },
]

export function buildRequestedByFields(user = {}) {
  return {
    requested_by_id: user.id ?? null,
    requested_by_name: user.name?.trim() ?? '',
    requested_by_email: user.email?.trim() ?? '',
    requested_by_type: user.type?.trim() ?? '',
  }
}

function formatRequestedAt(dateString) {
  if (!dateString) return ''

  const date = new Date(dateString)
  if (Number.isNaN(date.getTime())) return ''

  return date.toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

export function formatRequestedByParagraph(entry = {}) {
  const name = entry.requested_by_name?.trim()
  const email = entry.requested_by_email?.trim()
  const type = entry.requested_by_type?.trim()
  const requestedAt = formatRequestedAt(entry.requested_at || entry.created_at)

  if (!name && !email) {
    return 'Staff information is not available for this bill entry.'
  }

  const roleLabel = type ? type.replace(/_/g, ' ') : 'staff'
  let text = `This bill entry was requested by ${name || 'Unknown staff'} (${roleLabel})`

  if (requestedAt) {
    text += ` on ${requestedAt}`
  }

  text += '.'

  if (email) {
    text += ` Contact email: ${email}.`
  }

  return text
}

export function normalizeExpensePayment(payment) {
  const receiptUrls = Array.isArray(payment.receipt_urls)
    ? payment.receipt_urls.filter(Boolean)
    : payment.receipt_url
      ? [payment.receipt_url]
      : []

  return {
    ...payment,
    category_id: Number(payment.category_id),
    head_id: Number(payment.head_id),
    amount: Number(payment.amount ?? 0),
    paid_amount: Number(payment.paid_amount ?? 0),
    payable_remaining: Number(
      payment.payable_remaining ??
        Math.max(Number(payment.amount ?? 0) - Number(payment.paid_amount ?? 0), 0)
    ),
    payment_method: payment.payment_method ?? 'cash',
    payment_date: payment.payment_date ?? payment.created_at?.slice(0, 10) ?? '',
    status: payment.status ?? 'pending',
    approval_remarks: payment.approval_remarks ?? '',
    request_no: payment.request_no ?? '',
    receipt_url: payment.receipt_url || receiptUrls[0] || '',
    receipt_urls: receiptUrls,
    requested_by_id: payment.requested_by_id ?? null,
    requested_by_name: payment.requested_by_name ?? '',
    requested_by_email: payment.requested_by_email ?? '',
    requested_by_type: payment.requested_by_type ?? '',
    requested_at: payment.requested_at ?? payment.created_at ?? '',
    manager_approved_at: payment.manager_approved_at ?? '',
    manager_approved_by: payment.manager_approved_by ?? '',
    is_manual_request: Boolean(payment.is_manual_request),
    manual_approval_manager_id: payment.manual_approval_manager_id ?? null,
    manual_approval_manager_name: payment.manual_approval_manager_name ?? '',
    manual_approval_url: payment.manual_approval_url ?? '',
    payment_account_category: payment.payment_account_category ?? '',
    payment_account_type: payment.payment_account_type ?? '',
    payment_account_id: payment.payment_account_id ?? null,
    payment_account_name: payment.payment_account_name ?? '',
    demand_letter_id: payment.demand_letter_id ?? null,
    demand_letter: payment.demand_letter ?? '',
    expense_cost_type: payment.expense_cost_type ?? '',
    expense_cost_account_id: payment.expense_cost_account_id ?? null,
    expense_cost_account_name: payment.expense_cost_account_name ?? '',
    expense_cost_category_name: payment.expense_cost_category_name ?? '',
    linked_account_category: payment.linked_account_category ?? '',
    linked_account_id: payment.linked_account_id ?? null,
    linked_account_name: payment.linked_account_name ?? '',
    linked_account_type: payment.linked_account_type ?? '',
  }
}

function loadStoredBillEntries() {
  const billEntries = loadFinanceJson(financeStorageKeys.billEntries, null)

  if (Array.isArray(billEntries)) {
    return loadExpensePayments(billEntries)
  }

  const legacyEntries = loadFinanceJson(financeStorageKeys.expensePayments, initialExpensePayments)

  return loadExpensePayments(legacyEntries)
}

export { loadStoredBillEntries }

export function loadExpensePayments(saved) {
  if (!Array.isArray(saved) || saved.length === 0) {
    return structuredClone(initialExpensePayments)
  }

  return saved.map((payment) => normalizeExpensePayment(payment))
}
