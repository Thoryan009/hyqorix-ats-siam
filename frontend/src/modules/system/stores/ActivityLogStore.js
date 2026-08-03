import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useActivityLogStore = defineStore('ActivityLog', () => {

  const moduleName = 'ActivityLog'

  const {
    item,
    type,
    isModal,
    isViewModal,
    isEditModal,
    title,
    handleToggleModal,
    handleReset,
  } = useModalHelpers(moduleName)

  return {
    item,
    type,
    isModal,
    isViewModal,
    isEditModal,
    moduleName,
    title,
    handleToggleModal,
    handleReset,
  }
})
