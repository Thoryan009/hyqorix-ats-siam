const PAGE_SIZES = {
  portrait: 'A4 portrait',
  landscape: 'A4 landscape',
}

function buildPrintDocumentHtml({
  title = 'Report',
  bodyHtml = '',
  orientation = 'portrait',
  margin = '8mm',
} = {}) {
  const pageSize = PAGE_SIZES[orientation] ?? PAGE_SIZES.portrait
  const safeTitle = escapeHtml(title)

  return `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>${safeTitle}</title>
  <style>
    @page { size: ${pageSize}; margin: ${margin}; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 12px;
      color: #000;
      background: #fff;
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      line-height: 1.35;
    }
    h1 { font-size: 16px; margin: 0 0 4px; }
    .meta { font-size: 11px; color: #333; margin: 0 0 10px; }
    .toolbar {
      display: flex;
      gap: 8px;
      margin-bottom: 12px;
    }
    .toolbar button {
      border: 1px solid #cbd5e1;
      background: #f8fafc;
      color: #0f172a;
      border-radius: 6px;
      padding: 6px 12px;
      cursor: pointer;
      font-size: 12px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: auto;
    }
    th, td {
      border: 1px solid #94a3b8;
      padding: 3px 5px;
      vertical-align: top;
      color: #000 !important;
    }
    th {
      background: #e2e8f0;
      text-align: left;
      font-size: 10px;
      text-transform: uppercase;
    }
    td.num, th.num { text-align: right; white-space: nowrap; }
    td.center, th.center { text-align: center; }
    tr.group td {
      background: #f1f5f9;
      font-weight: 700;
    }
    tr.total td {
      background: #ecfdf5;
      font-weight: 700;
    }
    tr.section td {
      background: #dbeafe;
      font-weight: 700;
      text-transform: uppercase;
    }
    .empty {
      padding: 24px;
      text-align: center;
      color: #64748b;
    }
    .loading {
      padding: 48px 24px;
      text-align: center;
      color: #475569;
      font-size: 14px;
    }
    @media print {
      .toolbar { display: none !important; }
      thead { display: table-header-group; }
      tfoot { display: table-footer-group; }
      tr { page-break-inside: avoid; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <button type="button" onclick="window.print()">Print</button>
    <button type="button" onclick="window.close()">Close</button>
  </div>
  ${bodyHtml}
</body>
</html>`
}

/**
 * Must be called synchronously from a click handler.
 * Browsers block window.open() after await / network requests.
 */
export function openPrintWindow({
  title = 'Report',
  loadingText = 'Preparing print…',
  orientation = 'portrait',
  margin = '8mm',
} = {}) {
  const popup = window.open('about:blank', '_blank')
  if (!popup) {
    return null
  }

  try {
    popup.document.open()
    popup.document.write(
      buildPrintDocumentHtml({
        title,
        orientation,
        margin,
        bodyHtml: `<div class="loading">${escapeHtml(loadingText)}</div>`,
      }),
    )
    popup.document.close()
  } catch {
    try {
      popup.close()
    } catch {
      /* ignore */
    }
    return null
  }

  return popup
}

export function writePrintWindow(
  popup,
  {
    title = 'Report',
    bodyHtml = '',
    orientation = 'portrait',
    margin = '8mm',
    autoClose = false,
    autoPrint = true,
  } = {},
) {
  if (!popup || popup.closed) {
    return false
  }

  try {
    popup.document.open()
    popup.document.write(
      buildPrintDocumentHtml({
        title,
        bodyHtml,
        orientation,
        margin,
      }),
    )
    popup.document.close()
  } catch {
    return false
  }

  if (!autoPrint) {
    return true
  }

  const triggerPrint = () => {
    try {
      popup.focus()
      popup.print()
      if (autoClose) {
        popup.addEventListener('afterprint', () => {
          try {
            popup.close()
          } catch {
            /* ignore */
          }
        })
      }
    } catch {
      /* ignore */
    }
  }

  if (popup.document.readyState === 'complete') {
    window.setTimeout(triggerPrint, 120)
  } else {
    popup.onload = () => window.setTimeout(triggerPrint, 120)
  }

  return true
}

export function closePrintWindow(popup) {
  if (!popup || popup.closed) return
  try {
    popup.close()
  } catch {
    /* ignore */
  }
}

/**
 * Open a lightweight print window and print HTML.
 * Prefer openPrintWindow() + writePrintWindow() when data is loaded async.
 */
export function printHtmlDocument({
  title = 'Report',
  bodyHtml = '',
  orientation = 'portrait',
  margin = '8mm',
  autoClose = false,
  popup = null,
} = {}) {
  const target = popup || openPrintWindow({ title, orientation, margin })
  if (!target) {
    return false
  }

  return writePrintWindow(target, {
    title,
    bodyHtml,
    orientation,
    margin,
    autoClose,
  })
}

export function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

/** Build large HTML tables without blocking the UI for too long. */
export function joinHtmlChunks(items, renderItem, chunkSize = 400) {
  if (!items?.length) return ''

  const parts = []
  for (let i = 0; i < items.length; i += chunkSize) {
    const chunk = items.slice(i, i + chunkSize)
    parts.push(chunk.map(renderItem).join(''))
  }
  return parts.join('')
}

export function formatPrintAmount(value, { empty = '' } = {}) {
  if (value === null || value === undefined || value === '') return empty
  const num = Number(value)
  if (Number.isNaN(num)) return empty
  return num.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

export function formatPrintPeriod({ from, to, asOfLabel = 'As of', rangeLabel = 'Period' } = {}) {
  if (from && to) return `${rangeLabel}: ${from} → ${to}`
  if (to) return `${asOfLabel}: ${to}`
  if (from) return `${rangeLabel}: ${from} → …`
  return ''
}
