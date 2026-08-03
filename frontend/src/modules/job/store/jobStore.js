import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useJobStore = defineStore('job', () => {
  const { item, isModal, isViewModal, isEditModal, isDeleteModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Job'

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
