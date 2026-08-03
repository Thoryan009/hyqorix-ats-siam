import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useDepartmentStore = defineStore('Department', () => {

  const moduleName = 'Department'

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
