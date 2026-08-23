import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const usePartyStore = defineStore('party', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers('Party')

  const moduleName = 'Party'

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
