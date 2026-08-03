import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useTransactionReportStore = defineStore('transaction-report', () => {
  const { item, handleReset } = useModalHelpers()

  const moduleName = 'Transaction Report'

  return {
    item,
    moduleName,
    handleReset,
  }
})
