import {
  escapeHtml,
  formatPrintAmount,
  formatPrintPeriod,
  joinHtmlChunks,
  printHtmlDocument,
} from '@/shared/utils/printHtmlDocument'

function partyDisplayLabel(row) {
  const code = String(row?.party_code || row?.party_ref || '').trim()
  const name = String(row?.party_name || '').trim()

  if (code && name && code.toLowerCase() !== name.toLowerCase()) {
    return `${code} – ${name}`
  }

  return code || name || '—'
}

function reportHeader({ title, subtitle, period, total }) {
  return `
    <h1>${escapeHtml(title)}</h1>
    <p class="meta">
      ${subtitle ? `${escapeHtml(subtitle)}<br/>` : ''}
      ${period ? `${escapeHtml(period)} · ` : ''}
      ${escapeHtml(total)}
    </p>
  `
}

export function buildPartyLedgerPrintHtml({ rows = [], labels = {}, filters = {} } = {}) {
  const period = formatPrintPeriod({
    from: filters.from_date,
    to: filters.to_date,
    rangeLabel: labels.period || 'Period',
  })

  const header = reportHeader({
    title: labels.title || 'Party Ledger',
    subtitle: labels.subtitle || '',
    period,
    total: labels.total || `${rows.length} entries`,
  })

  if (!rows.length) {
    return `${header}<div class="empty">${escapeHtml(labels.empty || 'No entries found.')}</div>`
  }

  const columns = [
    { key: 'party', label: labels.party_ref || 'Party' },
    { key: 'party_type', label: labels.party_type || 'Type' },
    { key: 'date', label: labels.date || 'Date' },
    { key: 'je_no', label: labels.je_no || 'JE No' },
    { key: 'particulars', label: labels.particulars || 'Particulars' },
    { key: 'account_code', label: labels.account_code || 'Account Code' },
    { key: 'account_name', label: labels.account_name || 'Account Name' },
    { key: 'debit', label: labels.debit || 'Debit', className: 'num' },
    { key: 'credit', label: labels.credit || 'Credit', className: 'num' },
    { key: 'running_balance', label: labels.running_balance || 'Balance', className: 'num' },
    { key: 'balance_type', label: labels.balance_type || 'Dr/Cr', className: 'center' },
  ]

  const thead = `<tr>${columns
    .map((col) => `<th class="${col.className || ''}">${escapeHtml(col.label)}</th>`)
    .join('')}</tr>`

  const items = []
  let lastPartyKey = ''

  rows.forEach((row) => {
    const partyKey = row.party_ref_key || `${row.party_type}::${row.party_ref}`
    if (partyKey !== lastPartyKey) {
      items.push({ kind: 'group', row })
      lastPartyKey = partyKey
    }
    items.push({ kind: 'entry', row })
  })

  const tbody = joinHtmlChunks(items, (item) => {
    if (item.kind === 'group') {
      return `<tr class="group"><td colspan="${columns.length}">${escapeHtml(
        `${item.row.party_type || ''} · ${partyDisplayLabel(item.row)}`,
      )}</td></tr>`
    }

    const row = item.row
    return `<tr>
      <td>${escapeHtml(partyDisplayLabel(row))}</td>
      <td>${escapeHtml(row.party_type || '')}</td>
      <td>${escapeHtml(row.date_label || row.date || '')}</td>
      <td>${escapeHtml(row.je_no || '')}</td>
      <td>${escapeHtml(row.particulars || '')}</td>
      <td>${escapeHtml(row.account_code || '—')}</td>
      <td>${escapeHtml(row.account_name || '—')}</td>
      <td class="num">${escapeHtml(formatPrintAmount(row.debit, { empty: '' }))}</td>
      <td class="num">${escapeHtml(formatPrintAmount(row.credit, { empty: '' }))}</td>
      <td class="num">${escapeHtml(formatPrintAmount(row.running_balance))}</td>
      <td class="center">${escapeHtml(row.balance_type || '')}</td>
    </tr>`
  })

  return `${header}<table><thead>${thead}</thead><tbody>${tbody}</tbody></table>`
}

export function buildGeneralLedgerPrintHtml({ rows = [], labels = {}, filters = {} } = {}) {
  const period = formatPrintPeriod({
    from: filters.from_date,
    to: filters.to_date,
    rangeLabel: labels.period || 'Period',
  })

  const header = reportHeader({
    title: labels.title || 'General Ledger',
    subtitle: labels.subtitle || '',
    period,
    total: labels.total || `${rows.length} entries`,
  })

  if (!rows.length) {
    return `${header}<div class="empty">${escapeHtml(labels.empty || 'No entries found.')}</div>`
  }

  const columns = [
    { key: 'account_code', label: labels.account_code || 'Account Code' },
    { key: 'account_name', label: labels.account_name || 'Account Name' },
    { key: 'date', label: labels.date || 'Date' },
    { key: 'je_no', label: labels.je_no || 'JE No' },
    { key: 'particulars', label: labels.particulars || 'Particulars' },
    { key: 'party_ref', label: labels.party_ref || 'Party' },
    { key: 'debit', label: labels.debit || 'Debit', className: 'num' },
    { key: 'credit', label: labels.credit || 'Credit', className: 'num' },
    { key: 'running_balance', label: labels.running_balance || 'Balance', className: 'num' },
    { key: 'balance_type', label: labels.balance_type || 'Dr/Cr', className: 'center' },
  ]

  const thead = `<tr>${columns
    .map((col) => `<th class="${col.className || ''}">${escapeHtml(col.label)}</th>`)
    .join('')}</tr>`

  const items = []
  let lastAccountKey = ''

  rows.forEach((row) => {
    const accountKey = `${row.account_id ?? ''}::${row.account_code}::${row.account_name}`
    if (accountKey !== lastAccountKey) {
      items.push({ kind: 'group', row })
      lastAccountKey = accountKey
    }
    items.push({ kind: 'entry', row })
  })

  const tbody = joinHtmlChunks(items, (item) => {
    if (item.kind === 'group') {
      return `<tr class="group"><td colspan="${columns.length}">${escapeHtml(
        `${item.row.account_code || ''} · ${item.row.account_name || ''}`,
      )}</td></tr>`
    }

    const row = item.row
    const balance =
      row.balance_type === 'Cr'
        ? `(${formatPrintAmount(row.running_balance)})`
        : formatPrintAmount(row.running_balance)

    return `<tr>
      <td>${escapeHtml(row.account_code || '')}</td>
      <td>${escapeHtml(row.account_name || '')}</td>
      <td>${escapeHtml(row.date_label || row.date || '')}</td>
      <td>${escapeHtml(row.je_no || '')}</td>
      <td>${escapeHtml(row.particulars || '')}</td>
      <td>${escapeHtml(partyDisplayLabel(row))}</td>
      <td class="num">${escapeHtml(formatPrintAmount(row.debit, { empty: '' }))}</td>
      <td class="num">${escapeHtml(formatPrintAmount(row.credit, { empty: '' }))}</td>
      <td class="num">${escapeHtml(balance)}</td>
      <td class="center">${escapeHtml(row.balance_type || '')}</td>
    </tr>`
  })

  return `${header}<table><thead>${thead}</thead><tbody>${tbody}</tbody></table>`
}

export function printPartyLedgerReport({ rows, labels, filters, popup = null }) {
  return printHtmlDocument({
    title: labels?.title || 'Party Ledger',
    orientation: 'landscape',
    margin: '8mm',
    popup,
    bodyHtml: buildPartyLedgerPrintHtml({ rows, labels, filters }),
  })
}

export function printGeneralLedgerReport({ rows, labels, filters, popup = null }) {
  return printHtmlDocument({
    title: labels?.title || 'General Ledger',
    orientation: 'landscape',
    margin: '8mm',
    popup,
    bodyHtml: buildGeneralLedgerPrintHtml({ rows, labels, filters }),
  })
}
