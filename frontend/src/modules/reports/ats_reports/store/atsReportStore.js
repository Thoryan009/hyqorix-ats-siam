import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useAtsReportStore = defineStore('ats-report', () => {
  const { item, handleReset } = useModalHelpers()

  const moduleName = 'Ats Report'

  return {
    item,
    moduleName,
    handleReset,
  }
})
