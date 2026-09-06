import { nextTick, ref } from 'vue'
import { printWithOrientation } from '@/shared/utils/printOrientation'

/**
 * In-page statement print for compact financial reports
 * (gross profit / income statement / balance sheet).
 */
export function useStatementPrint(printRootId, { orientation = 'portrait', margin = '10mm' } = {}) {
  const printing = ref(false)

  async function printStatement() {
    if (printing.value) return
    printing.value = true
    try {
      await nextTick()
      printWithOrientation(orientation, margin, printRootId)
    } finally {
      window.setTimeout(() => {
        printing.value = false
      }, 400)
    }
  }

  return { printing, printStatement }
}
