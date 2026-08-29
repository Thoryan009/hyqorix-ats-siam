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
  receipt_path: [],
  receipt_preview: [],
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

export function getOptionLabel(options, id) {
  if (!id) return ''
  return options.find((option) => String(option.id) === String(id))?.name ?? String(id)
}

export function getTransactionTypeLabel(id) {
  if (!id) return ''
  return String(id)
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

export function getCostTypeLabel(id) {
  return getOptionLabel(costTypeOptions, id)
}

export function getProjectLabel(id, options = []) {
  if (!id) return ''
  const match = options.find((option) => String(option.id) === String(id))
  if (match?.job_name) return match.job_name
  if (match?.name) return match.name
  return String(id)
}

export function formatJobSelectOptions(jobs = []) {
  return jobs.map((job) => ({
    id: `job:${job.id}`,
    name: `${job.job_name} · ${job.application_count} Applications`,
    job_name: job.job_name,
    application_count: job.application_count,
  }))
}

export function formatDemandLetterSelectOptions(demandLetters = []) {
  return demandLetters.map((item) => ({
    id: `dl:${item.id}`,
    name: `${item.name} · ${item.job_count} Jobs · ${item.application_count} Applications`,
    demand_letter_name: item.name,
    job_count: item.job_count,
    application_count: item.application_count,
  }))
}

export function filterJobOption(option, query) {
  const q = String(query || '').toLowerCase()
  if (!q) return true
  return [option?.name, option?.job_name, option?.application_count]
    .filter((value) => value !== undefined && value !== null && value !== '')
    .some((value) => String(value).toLowerCase().includes(q))
}

export function filterDemandLetterOption(option, query) {
  const q = String(query || '').toLowerCase()
  if (!q) return true
  return [
    option?.name,
    option?.demand_letter_name,
    option?.job_count,
    option?.application_count,
  ]
    .filter((value) => value !== undefined && value !== null && value !== '')
    .some((value) => String(value).toLowerCase().includes(q))
}

export const PROJECT_SOURCE_JOB = 'job'
export const PROJECT_SOURCE_DEMAND_LETTER = 'demand_letter'

export function parseProjectId(value) {
  const raw = String(value ?? '')

  if (!raw) {
    return { type: PROJECT_SOURCE_JOB, id: '' }
  }

  if (raw.startsWith('job:')) {
    return { type: PROJECT_SOURCE_JOB, id: raw }
  }

  if (raw.startsWith('dl:')) {
    return { type: PROJECT_SOURCE_DEMAND_LETTER, id: raw }
  }

  if (/^\d+$/.test(raw)) {
    return { type: PROJECT_SOURCE_JOB, id: `job:${raw}` }
  }

  return { type: PROJECT_SOURCE_JOB, id: raw }
}

export function normalizeProjectId(value) {
  const parsed = parseProjectId(value)
  return parsed.id
}

export function isJournalLineEmpty(line) {
  const accountId = String(line?.account_id ?? '').trim()
  const costType = String(line?.cost_type ?? '').trim()
  const debit = Number(line?.debit) || 0
  const credit = Number(line?.credit) || 0

  return !accountId && !costType && debit <= 0 && credit <= 0
}

export function getFilledJournalLines(lines) {
  return (lines ?? []).filter((line) => !isJournalLineEmpty(line))
}

export function normalizeReceiptFiles(value) {
  if (Array.isArray(value)) {
    return value.filter((item) => item instanceof File)
  }
  if (value instanceof File) {
    return [value]
  }
  return []
}

export function appendReceiptFiles(payload, form) {
  const files = normalizeReceiptFiles(form?.receipt_path)
  if (files.length) {
    payload.receipt_path = files
  }
  return payload
}

export function buildJournalPayload(form, lines, status) {
  const filledLines = getFilledJournalLines(lines)

  const payload = {
    voucher_date: form.voucher_date,
    transaction_type: form.transaction_type,
    reference_no: form.reference_no || null,
    party_type: form.party_type || null,
    party_id: form.party_id || null,
    project_id: form.project_id ? String(form.project_id) : null,
    narration: form.narration || null,
    status,
    lines: filledLines.map((line) => ({
      account_id: line.account_id,
      sub_ledger: line.sub_ledger || null,
      cost_type: line.cost_type || null,
      debit: Number(line.debit) || 0,
      credit: Number(line.credit) || 0,
    })),
  }

  return appendReceiptFiles(payload, form)
}

export function buildPayPayload(form, lines) {
  const payload = buildJournalPayload(form, lines, 'posted')
  delete payload.status
  return payload
}

export function mapJournalToForm(journal) {
  const receiptUrls = Array.isArray(journal?.receipt_urls)
    ? journal.receipt_urls.filter(Boolean)
    : journal?.receipt_url
      ? [journal.receipt_url]
      : []

  return {
    voucher_no: journal?.voucher_no || '—',
    status: journal?.status || 'Approved',
    voucher_date: journal?.voucher_date || todayIsoDate(),
    transaction_type: journal?.transaction_type || '',
    reference_no: journal?.reference_no || '',
    party_type: journal?.party_type || '',
    party_id: journal?.party_id || '',
    project_id: normalizeProjectId(journal?.project_id),
    narration: journal?.narration || '',
    manager_comment: journal?.manager_comment || '',
    receipt_path: [],
    receipt_preview: [],
    receipt_urls: receiptUrls,
  }
}

export function mapJournalToLines(journal) {
  const sourceLines = Array.isArray(journal?.lines) ? journal.lines : []
  if (!sourceLines.length) {
    return defaultJournalLines.map((line) => ({ ...line }))
  }

  return sourceLines.map((line, index) => ({
    id: line.id || index + 1,
    account_id: line.account_id || '',
    sub_ledger: line.sub_ledger || '',
    cost_type: line.cost_type || '',
    debit: Number(line.debit) > 0 ? line.debit : '',
    credit: Number(line.credit) > 0 ? line.credit : '',
  }))
}

export function validateJournalForm(form, lines) {
  const errors = []

  if (!form.voucher_date) {
    errors.push({ key: 'journals.error_voucher_date_required' })
  }

  if (!form.transaction_type) {
    errors.push({ key: 'journals.error_transaction_type_required' })
  }

  const emptyRowNumbers = []
  const filled = []

  ;(lines ?? []).forEach((line, index) => {
    const rowNo = index + 1
    if (isJournalLineEmpty(line)) {
      emptyRowNumbers.push(rowNo)
      return
    }
    filled.push({ line, rowNo })
  })

  if (!filled.length) {
    errors.push({ key: 'journals.error_min_lines' })
    return errors
  }

  if (emptyRowNumbers.length) {
    errors.unshift({
      key:
        emptyRowNumbers.length === 1
          ? 'journals.error_empty_row'
          : 'journals.error_empty_rows',
      params: {
        row: emptyRowNumbers[0],
        rows: emptyRowNumbers.join(', '),
      },
    })
  }

  filled.forEach(({ line, rowNo }) => {
    if (!line.account_id) {
      errors.push({
        key: 'journals.error_row_account_required',
        params: { row: rowNo },
      })
    }

    const debit = Number(line.debit) || 0
    const credit = Number(line.credit) || 0

    if (debit > 0 && credit > 0) {
      errors.push({
        key: 'journals.error_row_both_amounts',
        params: { row: rowNo },
      })
    } else if (debit <= 0 && credit <= 0) {
      errors.push({
        key: 'journals.error_row_amount_required',
        params: { row: rowNo },
      })
    }
  })

  const totalDebit = filled.reduce((sum, item) => sum + (Number(item.line.debit) || 0), 0)
  const totalCredit = filled.reduce((sum, item) => sum + (Number(item.line.credit) || 0), 0)

  if (filled.length < 2) {
    errors.push({ key: 'journals.error_min_lines' })
  }

  if (Math.abs(totalDebit - totalCredit) > 0.009) {
    errors.push({ key: 'journals.error_unbalanced' })
  }

  return errors
}

export function flattenJournalRows(journals = []) {
  return journals.flatMap((journal) => {
    const lines = Array.isArray(journal.lines) && journal.lines.length ? journal.lines : [{}]

    return lines.map((line, index) => ({
      id: `${journal.id}-${line.id ?? index}`,
      je_no: journal.voucher_no,
      date: journal.voucher_date_label || journal.voucher_date,
      transaction: journal.narration || journal.transaction_type_name || getTransactionTypeLabel(journal.transaction_type),
      account_code: line.account_code || '',
      account_name: line.account_name || '',
      party_ref: journal.party_code || journal.party_name || journal.reference_no || '',
      debit: Number(line.debit) > 0 ? Number(line.debit) : null,
      credit: Number(line.credit) > 0 ? Number(line.credit) : null,
      cost_class: getCostTypeLabel(line.cost_type),
      project_client: journal.project_name || getProjectLabel(journal.project_id),
      status: journal.status,
    }))
  })
}
