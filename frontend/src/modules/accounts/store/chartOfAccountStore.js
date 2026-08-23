import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useChartOfAccountStore = defineStore('chartOfAccount', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers('Chart of Account')

  const moduleName = 'Chart of Account'

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    moduleName,
    handleToggleModal,
    handleReset,
  }
})
