import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useApplicationReportStore = defineStore('application-report', () => {
  const { item, handleReset } = useModalHelpers()

  const moduleName = 'Applicant Report'

  return {
    item,
    moduleName,
    handleReset,
  }
})
