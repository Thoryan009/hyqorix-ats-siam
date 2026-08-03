import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { ref } from 'vue'

export const useTransactionStore = defineStore('transaction', () => {
  const { item, isModal, isViewModal, isEditModal, isDeleteModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Transaction'
  const showReceipt = ref(true)
  const receiptData = ref({})

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    isDeleteModal,
    showReceipt,
    receiptData,
    moduleName,
    handleToggleModal,
    handleReset,
  }
})
