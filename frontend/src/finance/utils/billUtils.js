const ones = [
  '',
  'One',
  'Two',
  'Three',
  'Four',
  'Five',
  'Six',
  'Seven',
  'Eight',
  'Nine',
  'Ten',
  'Eleven',
  'Twelve',
  'Thirteen',
  'Fourteen',
  'Fifteen',
  'Sixteen',
  'Seventeen',
  'Eighteen',
  'Nineteen',
]

const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety']

function convertBelowThousand(num) {
  let words = ''

  if (num >= 100) {
    words += `${ones[Math.floor(num / 100)]} Hundred`
    num %= 100
    if (num > 0) words += ' '
  }

  if (num >= 20) {
    words += tens[Math.floor(num / 10)]
    num %= 10
    if (num > 0) words += ` ${ones[num]}`
  } else if (num > 0) {
    words += ones[num]
  }

  return words.trim()
}

export function numberToWords(amount) {
  const value = Math.floor(Number(amount) || 0)
  if (value === 0) return 'Taka Zero Only'

  const crore = Math.floor(value / 10000000)
  const lakh = Math.floor((value % 10000000) / 100000)
  const thousand = Math.floor((value % 100000) / 1000)
  const remainder = value % 1000

  const parts = []

  if (crore) parts.push(`${convertBelowThousand(crore)} Crore`)
  if (lakh) parts.push(`${convertBelowThousand(lakh)} Lakh`)
  if (thousand) parts.push(`${convertBelowThousand(thousand)} Thousand`)
  if (remainder) parts.push(convertBelowThousand(remainder))

  return `Taka ${parts.join(' ')} Only`
}

export const candidateCostFields = [
  { key: 'medical_fee', label: 'Medical Fee' },
  { key: 'mofa_fee', label: 'MOFA Fee' },
  { key: 'takamol_fee', label: 'Takamol Fee' },
  { key: 'visa_cost', label: 'Visa Cost' },
  { key: 'principal_cost', label: 'Principal Cost' },
  { key: 'air_ticket_cost', label: 'Air Ticket Cost' },
]

export function getCandidateCosts(candidate) {
  return candidate?.costs ?? {}
}

export function getCandidateSalePrice(candidate) {
  return Number(candidate?.sale_price ?? candidate?.charge ?? 0)
}

export function getCandidateTotalCost(candidate) {
  return candidateCostFields.reduce(
    (sum, field) => sum + Number(getCandidateCosts(candidate)[field.key] || 0),
    0
  )
}

export function getCandidateProfit(candidate) {
  return getCandidateSalePrice(candidate) - getCandidateTotalCost(candidate)
}

export function syncCandidateTotals(candidate) {
  if (!candidate) return candidate
  candidate.total_cost = getCandidateTotalCost(candidate)
  candidate.profit = getCandidateProfit(candidate)
  return candidate
}

export function formatCurrency(amount, currency = 'BDT') {
  const value = Number(amount) || 0
  const formatted = new Intl.NumberFormat('en-IN', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value)

  if (currency === 'BDT') {
    return `৳${formatted}`
  }

  return String(Math.round(value))
}

export function parseBillDate(dateString) {
  if (!dateString) return new Date()
  const [year, month, day] = dateString.split('-').map(Number)
  if (year && month && day) return new Date(year, month - 1, day)
  return new Date(dateString)
}

export function formatDisplayDate(dateString) {
  if (!dateString) return ''
  const date = parseBillDate(dateString)
  const dd = String(date.getDate()).padStart(2, '0')
  const mm = String(date.getMonth() + 1).padStart(2, '0')
  const yyyy = date.getFullYear()
  return `${dd}-${mm}-${yyyy}`
}

export function formatBillDate(dateString) {
  return formatDisplayDate(dateString)
}

export function formatDirectExpenseParticularDescription(expenseHeadName = '') {
  const head = String(expenseHeadName || '').trim() || 'Head'
  return `${head} Bill -`
}

export function getBillBatchKey(entry) {
  const requestNo = String(entry?.request_no || '').trim()
  if (requestNo) return `req:${requestNo}`
  const batchRef = String(entry?.batch_ref || '').trim()
  if (batchRef) return `batch:${batchRef}`
  return `id:${entry?.id ?? 'unknown'}`
}

export function getBillBatchLabel(entry) {
  const requestNo = String(entry?.request_no || '').trim()
  if (requestNo) return requestNo
  const batchRef = String(entry?.batch_ref || '').trim()
  if (batchRef) return batchRef
  return entry?.voucher_no || entry?.reference_no || `Bill #${entry?.id || '—'}`
}

export function generateBillNo(billDate, existingBillNos = []) {
  const date = parseBillDate(billDate)
  const yearSuffix = String(date.getFullYear()).slice(-2)
  const pattern = new RegExp(`^A(\\d{3})/${yearSuffix}$`, 'i')

  let maxSeq = 0
  for (const billNo of existingBillNos) {
    const match = String(billNo).match(pattern)
    if (match) maxSeq = Math.max(maxSeq, Number(match[1]))
  }

  return `A${String(maxSeq + 1).padStart(3, '0')}/${yearSuffix}`
}
