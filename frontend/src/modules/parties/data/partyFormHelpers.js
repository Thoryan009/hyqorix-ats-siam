export function createEmptyPartyRow(id) {
  return {
    id,
    source_id: '',
    code: '',
    name: '',
    opening_debit: 0,
    opening_credit: 0,
    status: 'active',
    remarks: '',
  }
}

export function createDefaultPartyRows(count = 2, startId = 1) {
  return Array.from({ length: count }, (_, index) => createEmptyPartyRow(startId + index))
}

export function isPartyRowEmpty(row, { requireSource = false } = {}) {
  const code = String(row?.code ?? '').trim()
  const name = String(row?.name ?? '').trim()
  const remarks = String(row?.remarks ?? '').trim()
  const openingDebit = Number(row?.opening_debit) || 0
  const openingCredit = Number(row?.opening_credit) || 0
  const hasSource = Boolean(row?.source_id)

  if (requireSource && hasSource) return false
  if (code || name || remarks) return false
  if (openingDebit > 0 || openingCredit > 0) return false

  return true
}

export function buildBulkPartyPayload(partyType, rows) {
  return rows.map((row) => {
    const payload = {
      type: partyType,
      code: String(row.code ?? '').trim(),
      name: String(row.name ?? '').trim(),
      opening_debit: Number(row.opening_debit) || 0,
      opening_credit: Number(row.opening_credit) || 0,
      status: row.status || 'active',
      remarks: String(row.remarks ?? '').trim(),
    }

    if (row.source_id) {
      payload.source_id = Number(row.source_id)
    }

    return payload
  })
}

export function validateBulkPartyRows(partyType, rows, { requireSource = false } = {}) {
  const errors = []

  if (!partyType) {
    errors.push({ key: 'parties.bulk_error_party_type_required' })
    return errors
  }

  if (!rows.length) {
    errors.push({ key: 'parties.bulk_error_min_one_row' })
    return errors
  }

  const emptyRowNumbers = []
  const codes = new Set()

  rows.forEach((row, index) => {
    const rowNo = index + 1
    const code = String(row.code ?? '').trim()
    const name = String(row.name ?? '').trim()
    const empty = isPartyRowEmpty(row, { requireSource })

    if (empty) {
      emptyRowNumbers.push(rowNo)
      return
    }

    if (requireSource && !row.source_id) {
      errors.push({
        key: 'parties.bulk_error_row_select_source',
        params: { row: rowNo, type: partyType },
      })
    }

    if (!code && !name) {
      errors.push({
        key: 'parties.bulk_error_row_empty_required',
        params: { row: rowNo },
      })
      return
    }

    if (!code) {
      errors.push({
        key: 'parties.bulk_error_row_party_id_required',
        params: { row: rowNo },
      })
    }

    if (!name) {
      errors.push({
        key: 'parties.bulk_error_row_name_required',
        params: { row: rowNo },
      })
    }

    if (code) {
      const normalized = code.toLowerCase()
      if (codes.has(normalized)) {
        errors.push({
          key: 'parties.bulk_error_row_duplicate_party_id',
          params: { row: rowNo, code },
        })
      }
      codes.add(normalized)
    }
  })

  if (emptyRowNumbers.length) {
    const filledCount = rows.length - emptyRowNumbers.length

    if (filledCount > 0) {
      errors.unshift({
        key:
          emptyRowNumbers.length === 1
            ? 'parties.bulk_error_empty_row'
            : 'parties.bulk_error_empty_rows',
        params: {
          row: emptyRowNumbers[0],
          rows: emptyRowNumbers.join(', '),
        },
      })
    } else {
      errors.unshift({ key: 'parties.bulk_error_all_rows_empty' })
    }
  }

  return errors
}
