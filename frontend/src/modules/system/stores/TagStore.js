import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'

export const useTagStore = defineStore('tag', () => {
  const { item, type, isModal, isViewModal, isEditModal, title, handleToggleModal, handleReset } =
    useModalHelpers('Tag')

  const moduleName = 'Tag'

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
