import { isExpenseCostAccountsType } from '@/finance/config/expenseCostAccountConfigs'
import { isDuePaymentMethod } from '@/finance/data/billApprovalAccountData'
import { isExpenseCostAccountCategory } from '@/finance/data/expenseHeadAccountLinkData'

export function isExpenseAccountBill(entry = {}) {
  entry = entry ?? {}
  if (entry.expense_cost_type && isExpenseCostAccountsType(entry.expense_cost_type)) {
    return Boolean(entry.expense_cost_account_id)
  }

  const category = entry.linked_account_category
  if (category && isExpenseCostAccountCategory(category)) {
    return Boolean(entry.linked_account_id || entry.expense_cost_account_id)
  }

  return false
}

export function getPayableRemainingAmount(entry = {}) {
  entry = entry ?? {}
  if (entry.payable_remaining != null && entry.payable_remaining !== '') {
    return Math.max(Number(entry.payable_remaining) || 0, 0)
  }

  const total = Number(entry.amount) || 0
  const paid = Number(entry.paid_amount) || 0

  return Math.max(Math.round((total - paid) * 100) / 100, 0)
}

export function isPayableBill(entry = {}) {
  entry = entry ?? {}
  return (
    entry.status === 'approved' &&
    isDuePaymentMethod(entry.payment_method) &&
    isExpenseAccountBill(entry) &&
    getPayableRemainingAmount(entry) > 0
  )
}

export function isPaidBill(entry = {}) {
  entry = entry ?? {}
  return entry.status === 'approved' && !isDuePaymentMethod(entry.payment_method)
}

export function isProcessedBill(entry = {}) {
  entry = entry ?? {}
  return entry.status === 'rejected' || isPaidBill(entry)
}
