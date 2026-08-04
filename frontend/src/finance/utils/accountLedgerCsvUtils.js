import { formatDisplayDate } from './billUtils'
import { ACCOUNT_CATEGORIES } from '@/finance/data/accountCategoryCodes'

function getLedgerHeaders(category) {
  const normalized = String(category || '').toLowerCase()
  const isSystemLedger =
    normalized === ACCOUNT_CATEGORIES.CAPITAL ||
    normalized === ACCOUNT_CATEGORIES.AGENT_ADVANCED ||
    normalized === ACCOUNT_CATEGORIES.SALE ||
    normalized === ACCOUNT_CATEGORIES.BILLS_RECEIVABLE ||
    normalized === ACCOUNT_CATEGORIES.INCOME_RECEIVABLE
  const isIncomeHeadLedger =
    normalized === ACCOUNT_CATEGORIES.CLIENT_INCOME ||
    normalized === ACCOUNT_CATEGORIES.RECRUITMENT_INCOME ||
    normalized === ACCOUNT_CATEGORIES.OTHER_INCOME
  const isAssetOrLiabilitiesLedger =
    normalized === ACCOUNT_CATEGORIES.ASSET ||
    normalized === ACCOUNT_CATEGORIES.LIABILITIES
  const useAmountLabel = isIncomeHeadLedger || isAssetOrLiabilitiesLedger

  return [
    'Date',
    'Particular',
    'Voucher No',
    'Demand Letter',
    'Job',
    'Reference',
    isSystemLedger || isAssetOrLiabilitiesLedger
      ? 'DR'
      : isIncomeHeadLedger
        ? 'DR (Bill)'
        : 'DR (Payment)',
    'Discount',
    isSystemLedger || isAssetOrLiabilitiesLedger ? 'CR' : 'CR (Received)',
    'Payment Method',
    useAmountLabel ? 'Amount' : 'Balance',
    'Remarks',
  ]
}

function escapeCsvValue(value) {
  const text = String(value ?? '')
  if (text.includes(',') || text.includes('"') || text.includes('\n')) {
    return `"${text.replace(/"/g, '""')}"`
  }
  return text
}

function formatLedgerAmount(value) {
  const amount = Number(value) || 0
  return amount > 0 ? amount : ''
}

export function formatAccountLedgerCreditAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value <= 0) return '-'
  return formatCurrency(value)
}

export function formatAccountLedgerBalanceAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value === 0) return '-'
  return formatCurrency(value)
}

/** Income-head Amount column: absolute value + DR/CR label (no minus sign). */
export function isAccountLedgerAmountDebit(amount) {
  return Number(amount) < 0
}

export function formatAccountLedgerAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value === 0) return formatCurrency(0)
  if (value < 0) {
    return `${formatCurrency(Math.abs(value))} DR`
  }
  return `${formatCurrency(value)} CR`
}

function formatAccountLedgerCsvAmount(value) {
  const amount = Number(value) || 0
  if (amount === 0) return ''
  if (amount < 0) return `${Math.abs(amount)} DR`
  return `${amount} CR`
}

export function exportAccountLedgerCsv(entries, meta = {}) {
  const lines = []

  if (meta.accountName) {
    lines.push(['Account', meta.accountName].map(escapeCsvValue).join(','))
  }
  if (meta.accountLabel) {
    lines.push(['Account Label', meta.accountLabel].map(escapeCsvValue).join(','))
  }
  if (meta.fromDate || meta.toDate) {
    lines.push(
      [
        'Date Range',
        `${meta.fromDate ? formatDisplayDate(meta.fromDate) : 'Start'} to ${
          meta.toDate ? formatDisplayDate(meta.toDate) : 'End'
        }`,
      ]
        .map(escapeCsvValue)
        .join(',')
    )
  }

  if (lines.length) lines.push('')

  lines.push(getLedgerHeaders(meta.category).join(','))
  lines.push(
    ...entries.map((entry) =>
      [
        formatDisplayDate(entry.date),
        entry.particular,
        entry.voucher_no,
        entry.demand_letter,
        entry.job,
        entry.client_name,
        formatLedgerAmount(entry.dr_amount),
        formatLedgerAmount(entry.discount),
        formatLedgerAmount(entry.cr_amount),
        entry.payment_method,
        meta.useAmountLabel
          ? formatAccountLedgerCsvAmount(entry.balance)
          : Number(entry.balance) || 0,
        entry.remarks,
      ]
        .map(escapeCsvValue)
        .join(',')
    )
  )

  return `\uFEFF${lines.join('\n')}`
}

export function downloadAccountLedgerCsv(entries, meta = {}) {
  const accountSlug = meta.accountName
    ? meta.accountName.replace(/\s+/g, '-').toLowerCase()
    : 'account'
  const filename = `${accountSlug}-ledger.csv`
  const content = exportAccountLedgerCsv(entries, meta)
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}
