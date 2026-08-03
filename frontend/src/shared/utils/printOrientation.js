const PRINT_STYLE_ID = 'print-page-styles'

const PAGE_SIZES = {
  portrait: 'A4 portrait',
  landscape: 'A4 landscape',
}

function buildPrintStyles(orientation = 'portrait', margin = '8mm', printRootId = null) {
  const pageSize = PAGE_SIZES[orientation] ?? PAGE_SIZES.portrait

  let css = `@media print {
  @page {
    size: ${pageSize};
    margin: ${margin};
  }`

  if (printRootId) {
    css += `

  html,
  body {
    width: 100%;
    height: auto;
    margin: 0;
    padding: 0;
    overflow: visible;
    background: #fff;
  }

  body * {
    visibility: hidden;
  }

  #${printRootId},
  #${printRootId} * {
    visibility: visible;
  }

  #${printRootId} {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    max-width: 100%;
    color: #000 !important;
    background: #fff !important;
  }`
  }

  css += `
}`

  return css
}

export function applyPrintStyles(orientation = 'portrait', margin = '8mm', printRootId = null) {
  removePrintStyles()

  const style = document.createElement('style')
  style.id = PRINT_STYLE_ID
  style.textContent = buildPrintStyles(orientation, margin, printRootId)
  document.head.appendChild(style)
}

export function removePrintStyles() {
  document.getElementById(PRINT_STYLE_ID)?.remove()
}

/** @deprecated use removePrintStyles */
export const removePrintOrientation = removePrintStyles

/** @deprecated use applyPrintStyles */
export function applyPrintOrientation(orientation = 'portrait', margin = '8mm') {
  applyPrintStyles(orientation, margin, null)
}

export function printWithOrientation(
  orientation = 'portrait',
  margin = '8mm',
  printRootId = null
) {
  applyPrintStyles(orientation, margin, printRootId)

  const cleanup = () => {
    removePrintStyles()
    window.removeEventListener('afterprint', cleanup)
  }

  window.addEventListener('afterprint', cleanup)

  requestAnimationFrame(() => {
    setTimeout(() => {
      window.print()
    }, 50)
  })
}
