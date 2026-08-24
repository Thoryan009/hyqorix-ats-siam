import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const usePartyStore = defineStore('party', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers('Party')

  const moduleName = 'Party'
  const isBulkModal = ref(false)

  const closeBulkModal = () => {
    isBulkModal.value = false
  }

  const handleOpenBulkModal = () => {
    isModal.value = false
    isViewModal.value = false
    isEditModal.value = false
    item.value = null
    isBulkModal.value = true
  }

  const handleToggleModalWithBulk = (modalType, selectedItem = null) => {
    isBulkModal.value = false
    handleToggleModal(modalType, selectedItem)
  }

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    isBulkModal,
    moduleName,
    handleToggleModal: handleToggleModalWithBulk,
    handleOpenBulkModal,
    closeBulkModal,
    handleReset,
  }
})
