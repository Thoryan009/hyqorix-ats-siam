import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useJournalTransactionTypeStore = defineStore('journalTransactionType', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers('Transaction Type')

  const moduleName = 'Transaction Type'

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
