import { formatDisplayDate } from './billUtils'

const LEDGER_HEADERS = [
  'Date',
  'Particular',
  'Voucher No',
  'Demand Letter',
  'Job',
  'Client Name',
  'DR (Charge)',
  'Discount',
  'CR (Payment)',
  'Payment Method',
  'Balance',
  'Remarks',
]

const APPLICANT_LEDGER_HEADERS = [
  'Date',
  'Particular',
  'Voucher No',
  'Demand Letter',
  'Job',
  'Client Name',
  'DR (Charge)',
  'Discount',
  'CR (Payment)',
  'Payment Method',
  'Amount',
  'Remarks',
]

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

export function formatLedgerCreditAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value <= 0) return '-'
  // Credit-style display uses a leading minus; format absolute value to avoid "--".
  return `-${formatCurrency(Math.abs(value))}`
}

export function formatLedgerBalanceAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value === 0) return '-'
  // Credit-style display uses a leading minus; format absolute value to avoid "--".
  return `-${formatCurrency(Math.abs(value))}`
}

/** Expense head ledger running amount: debit (bills) negative/red, credit (payments) positive/green. */
export function isExpenseLedgerAmountDebit(amount) {
  return Number(amount) < 0
}

export function formatExpenseLedgerAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value === 0) return '—'
  if (value < 0) {
    return `${formatCurrency(Math.abs(value))} DR`
  }
  return `${formatCurrency(value)} CR`
}

/** Applicant ledger running amount: debit side is red DR, credit side green CR (no minus sign). */
export function isApplicantLedgerAmountDebit(amount) {
  return Number(amount) < 0
}

export function formatApplicantLedgerAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value === 0) return formatCurrency(0)
  if (value < 0) {
    return `${formatCurrency(Math.abs(value))} DR`
  }
  return `${formatCurrency(value)} CR`
}

function formatLedgerCsvCreditAmount(value) {
  const amount = Number(value) || 0
  return amount > 0 ? -amount : ''
}

function formatLedgerCsvBalanceAmount(value) {
  const amount = Number(value) || 0
  return amount !== 0 ? -Math.abs(amount) : ''
}

function formatApplicantLedgerCsvAmount(value) {
  const amount = Number(value) || 0
  if (amount === 0) return ''
  if (amount < 0) return `${Math.abs(amount)} DR`
  return `${amount} CR`
}

export function exportPartyLedgerCsv(entries, meta = {}) {
  const lines = []
  const useAmountColumn = Boolean(meta.useAmountLabel)
  const referenceLabel = meta.referenceColumnLabel || 'Client Name'
  const baseHeaders = useAmountColumn ? APPLICANT_LEDGER_HEADERS : LEDGER_HEADERS
  const headers = baseHeaders.map((header) =>
    header === 'Client Name' ? referenceLabel : header
  )

  if (meta.partyLabel && meta.partyName) {
    lines.push([meta.partyLabel, meta.partyName].map(escapeCsvValue).join(','))
  }
  if (meta.partyCode) {
    lines.push([`${meta.partyLabel || 'Party'} Code`, meta.partyCode].map(escapeCsvValue).join(','))
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

  lines.push(headers.join(','))
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
        formatLedgerCsvCreditAmount(entry.cr_amount),
        entry.payment_method,
        useAmountColumn
          ? formatApplicantLedgerCsvAmount(entry.balance)
          : formatLedgerCsvBalanceAmount(entry.balance),
        entry.remarks,
      ]
        .map(escapeCsvValue)
        .join(',')
    )
  )

  return `\uFEFF${lines.join('\n')}`
}

export function downloadPartyLedgerCsv(entries, meta = {}) {
  const slug = meta.partyCode ? meta.partyCode.replace(/\s+/g, '-') : 'party'
  const filename = `${slug}-ledger.csv`
  const content = exportPartyLedgerCsv(entries, meta)
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}

// Backward-compatible vendor exports
export const exportVendorLedgerCsv = exportPartyLedgerCsv
export const downloadVendorLedgerCsv = downloadPartyLedgerCsv
