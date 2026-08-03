import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const usepermissionStore = defineStore('permission', () => {
  const {
    item,
    isModal,
    isViewModal,
    isEditModal,
    isDeleteModal,
    handleToggleModal,
    handleReset,
  } = useModalHelpers()

  const moduleName = 'permission'

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
