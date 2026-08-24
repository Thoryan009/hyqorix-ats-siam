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

export function buildBulkPartyPayload(partyType, rows) {
  return rows.map((row) => ({
    type: partyType,
    code: String(row.code ?? '').trim(),
    name: String(row.name ?? '').trim(),
    opening_debit: Number(row.opening_debit) || 0,
    opening_credit: Number(row.opening_credit) || 0,
    status: row.status || 'active',
    remarks: String(row.remarks ?? '').trim(),
  }))
}

export function validateBulkPartyRows(partyType, rows, { requireSource = false } = {}) {
  const errors = []

  if (!partyType) {
    errors.push('Party type is required.')
    return errors
  }

  if (!rows.length) {
    errors.push('Add at least one party row.')
    return errors
  }

  const codes = new Set()

  rows.forEach((row, index) => {
    const rowNo = index + 1
    const code = String(row.code ?? '').trim()
    const name = String(row.name ?? '').trim()

    if (requireSource && !row.source_id) {
      errors.push(`Row ${rowNo}: select a ${partyType.toLowerCase()}.`)
    }

    if (!code) {
      errors.push(`Row ${rowNo}: party ID is required.`)
    }

    if (!name) {
      errors.push(`Row ${rowNo}: name is required.`)
    }

    if (code) {
      if (codes.has(code.toLowerCase())) {
        errors.push(`Row ${rowNo}: duplicate party ID "${code}" in this batch.`)
      }
      codes.add(code.toLowerCase())
    }
  })

  return errors
}
