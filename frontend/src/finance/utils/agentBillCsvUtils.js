import { getBillPreviewContext } from '../data/agentBillData'
import {
  candidateCostFields,
  formatDisplayDate,
  generateBillNo,
  getCandidateCosts,
  getCandidateProfit,
  getCandidateSalePrice,
  getCandidateTotalCost,
  parseBillDate,
} from './billUtils'

const EXPORT_HEADERS = [
  'Bill No',
  'Bill Date',
  'Agent Code',
  'Agent Name',
  'Job Code',
  'Job Title',
  'Candidates',
  'Total Amount',
  'Paid Amount',
  'Currency',
  'Status',
]

function escapeCsvValue(value) {
  const text = String(value ?? '')
  if (text.includes(',') || text.includes('"') || text.includes('\n')) {
    return `"${text.replace(/"/g, '""')}"`
  }
  return text
}

export function parseImportBillDate(value) {
  if (!value) return new Date().toISOString().slice(0, 10)

  const trimmed = String(value).trim()
  if (/^\d{4}-\d{2}-\d{2}$/.test(trimmed)) return trimmed

  const parts = trimmed.split('-')
  if (parts.length === 3 && parts[0].length === 2) {
    const [day, month, year] = parts
    return `${year}-${month}-${day}`
  }

  const date = parseBillDate(trimmed)
  if (!Number.isNaN(date.getTime())) {
    const yyyy = date.getFullYear()
    const mm = String(date.getMonth() + 1).padStart(2, '0')
    const dd = String(date.getDate()).padStart(2, '0')
    return `${yyyy}-${mm}-${dd}`
  }

  return trimmed
}

function normalizeRow(row) {
  const normalized = {}

  Object.entries(row).forEach(([key, value]) => {
    const normalizedKey = key
      .trim()
      .toLowerCase()
      .replace(/[()]/g, '')
      .replace(/\s+/g, '_')
      .replace(/_+/g, '_')

    normalized[normalizedKey] = value
  })

  return normalized
}

export function exportAgentBillsCsv(bills) {
  const lines = [
    EXPORT_HEADERS.join(','),
    ...bills.map((bill) =>
      [
        bill.bill_no,
        formatDisplayDate(bill.bill_date),
        bill.agent_code,
        bill.agent_name,
        bill.job_code,
        bill.job_title,
        bill.candidate_count,
        bill.total_amount,
        bill.paid_amount || 0,
        bill.currency || 'BDT',
        bill.status,
      ]
        .map(escapeCsvValue)
        .join(',')
    ),
  ]

  return `\uFEFF${lines.join('\n')}`
}

export function downloadAgentBillsCsv(bills, filename = 'agent-bills.csv') {
  const content = exportAgentBillsCsv(bills)
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}

export function exportSingleAgentBillCsv(bill) {
  const context = getBillPreviewContext(bill)
  const lines = []

  const summaryRows = [
    ['Bill No', context.billNo],
    ['Bill Date', formatDisplayDate(context.billDate)],
    ['Agent Code', context.agent?.agent_code || bill.agent_code],
    ['Agent Name', context.agent?.agent_name || bill.agent_name],
    ['Job Code', context.job?.job_code || bill.job_code],
    ['Job Title', context.job?.job_title || bill.job_title],
    ['Client', context.job?.client || '-'],
    ['Total Candidates', context.selectedCandidates.length],
    ['Total Amount', bill.total_amount],
    ['Paid Amount', bill.paid_amount || 0],
    ['Currency', bill.currency || 'BDT'],
    ['Status', bill.status],
  ]

  summaryRows.forEach(([label, value]) => {
    lines.push([label, value].map(escapeCsvValue).join(','))
  })

  lines.push('')
  lines.push(escapeCsvValue('Candidate Details'))

  const candidateHeaders = [
    'SL',
    'Passport No',
    'Candidate Name',
    'Status',
    ...candidateCostFields.map((field) => field.label),
    'Total Cost',
    'Sale Price',
    'Profit',
  ]
  lines.push(candidateHeaders.map(escapeCsvValue).join(','))

  context.selectedCandidates.forEach((candidate, index) => {
    lines.push(
      [
        index + 1,
        candidate.passport_no,
        candidate.candidate_name,
        candidate.status,
        ...candidateCostFields.map((field) => getCandidateCosts(candidate)[field.key] || 0),
        getCandidateTotalCost(candidate),
        getCandidateSalePrice(candidate),
        getCandidateProfit(candidate),
      ]
        .map(escapeCsvValue)
        .join(',')
    )
  })

  const totalCost = context.selectedCandidates.reduce(
    (sum, candidate) => sum + getCandidateTotalCost(candidate),
    0
  )
  const totalProfit = context.selectedCandidates.reduce(
    (sum, candidate) => sum + getCandidateProfit(candidate),
    0
  )

  lines.push('')
  lines.push(['Total Cost', totalCost].map(escapeCsvValue).join(','))
  lines.push(['Total Sale', context.totalAmount].map(escapeCsvValue).join(','))
  lines.push(['Total Profit', totalProfit].map(escapeCsvValue).join(','))

  return `\uFEFF${lines.join('\n')}`
}

export function downloadSingleAgentBillCsv(bill) {
  const content = exportSingleAgentBillCsv(bill)
  const filename = `${String(bill.bill_no).replace('/', '-')}.csv`
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  link.click()
  URL.revokeObjectURL(url)
}

export function parseImportedAgentBills(rows, existingBillNos = []) {
  const assignedBillNos = [...existingBillNos]

  return rows
    .map((row) => {
      const data = normalizeRow(row)
      const billDate = parseImportBillDate(data.bill_date)
      let billNo = data.bill_no

      if (!billNo) {
        billNo = generateBillNo(billDate, assignedBillNos)
      }

      assignedBillNos.push(billNo)

      return {
        bill_no: billNo,
        bill_date: billDate,
        agent_code: data.agent_code || '',
        agent_name: data.agent_name || '',
        job_code: data.job_code || '',
        job_title: data.job_title || '',
        candidate_count: Number(data.candidates || data.candidate_count || 0),
        total_amount: Number(data.total_amount || 0),
        paid_amount: Number(data.paid_amount || 0),
        currency: data.currency || 'BDT',
        status: data.status || 'Unpaid',
      }
    })
    .filter((bill) => bill.agent_code && bill.job_code && bill.total_amount > 0)
}
