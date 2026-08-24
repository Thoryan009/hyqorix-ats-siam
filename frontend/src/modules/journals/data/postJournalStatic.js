export const transactionTypeOptions = [
  { id: 'journal_voucher', name: 'Journal Voucher' },
  { id: 'direct_expense', name: 'Direct Expense' },
  { id: 'operating_expense', name: 'Operating Expense' },
  { id: 'recruitment_revenue', name: 'Recruitment Revenue' },
  { id: 'recruitment_refund', name: 'Recruitment Refund' },
  { id: 'asset_purchase', name: 'Asset Purchase' },
  { id: 'asset_return', name: 'Asset Return' },
  { id: 'staff_advance', name: 'Staff Advance' },
  { id: 'advance_adjustment', name: 'Advance Adjustment' },
  { id: 'owner_capital', name: 'Owner Capital' },
]

export const partyTypeOptions = [
  { id: '', name: 'None' },
  { id: 'Client', name: 'Client' },
  { id: 'Principal', name: 'Principal' },
  { id: 'Agent', name: 'Agent' },
  { id: 'Candidate', name: 'Candidate' },
  { id: 'Vendor', name: 'Vendor' },
  { id: 'Staff', name: 'Staff' },
  { id: 'Owner', name: 'Owner' },
]

export const projectOptions = [
  { id: 'general', name: 'General / No Project' },
  { id: 'CL001', name: 'CL001 – Client Project' },
  { id: 'demand_01', name: 'Demand – Manpower Batch A' },
]

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
  return getOptionLabel(transactionTypeOptions, id)
}

export function getCostTypeLabel(id) {
  return getOptionLabel(costTypeOptions, id)
}

export function getProjectLabel(id) {
  return getOptionLabel(projectOptions, id)
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

export function buildJournalPayload(form, lines, status) {
  const filledLines = getFilledJournalLines(lines)

  return {
    voucher_date: form.voucher_date,
    transaction_type: form.transaction_type,
    reference_no: form.reference_no || null,
    party_type: form.party_type || null,
    party_id: form.party_id || null,
    project_id: form.project_id || null,
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
      transaction: journal.narration || getTransactionTypeLabel(journal.transaction_type),
      account_code: line.account_code || '',
      account_name: line.account_name || '',
      party_ref: journal.party_code || journal.party_name || journal.reference_no || '',
      debit: Number(line.debit) > 0 ? Number(line.debit) : null,
      credit: Number(line.credit) > 0 ? Number(line.credit) : null,
      cost_class: getCostTypeLabel(line.cost_type),
      project_client: getProjectLabel(journal.project_id),
      status: journal.status,
    }))
  })
}
