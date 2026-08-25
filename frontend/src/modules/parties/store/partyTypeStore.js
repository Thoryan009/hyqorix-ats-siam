import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const usePartyTypeStore = defineStore('partyType', () => {
  const { item, isModal, isViewModal, isEditModal, handleToggleModal, handleReset } =
    useModalHelpers('Party Type')

  const moduleName = 'Party Type'

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
