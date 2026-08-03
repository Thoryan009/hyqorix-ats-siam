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
  'CR (Received)',
  'Payment Method',
  'Balance',
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
  return formatCurrency(value)
}

export function formatLedgerBalanceAmount(amount, formatCurrency) {
  const value = Number(amount) || 0
  if (value === 0) return formatCurrency(0)
  if (value < 0) {
    return `${formatCurrency(Math.abs(value))} DR`
  }
  return `${formatCurrency(value)} CR`
}

export function isAgentLedgerBalanceNegative(amount) {
  return Number(amount) < 0
}

function formatLedgerCsvCreditAmount(value) {
  const amount = Number(value) || 0
  return amount > 0 ? amount : ''
}

function formatLedgerCsvBalanceAmount(value) {
  const amount = Number(value) || 0
  if (amount === 0) return ''
  if (amount < 0) return `${Math.abs(amount)} DR`
  return `${amount} CR`
}

export function exportAgentLedgerCsv(entries, meta = {}) {
  const lines = []

  if (meta.agentName) {
    lines.push(['Agent', meta.agentName].map(escapeCsvValue).join(','))
  }
  if (meta.agentCode) {
    lines.push(['Agent Code', meta.agentCode].map(escapeCsvValue).join(','))
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

  lines.push(LEDGER_HEADERS.join(','))
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
        formatLedgerCsvBalanceAmount(entry.balance),
        entry.remarks,
      ]
        .map(escapeCsvValue)
        .join(',')
    )
  )

  return `\uFEFF${lines.join('\n')}`
}

export function downloadAgentLedgerCsv(entries, meta = {}) {
  const agentSlug = meta.agentCode ? meta.agentCode.replace(/\s+/g, '-') : 'agent'
  const filename = `${agentSlug}-ledger.csv`
  const content = exportAgentLedgerCsv(entries, meta)
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}
