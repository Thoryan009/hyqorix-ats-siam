import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useVendorTypeStore = defineStore('vendorType', () => {
  const { item, isModal, isViewModal, isEditModal, isDeleteModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Vendor Type'

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    isDeleteModal,
    moduleName,
    handleToggleModal,
    handleReset,
  }
})
