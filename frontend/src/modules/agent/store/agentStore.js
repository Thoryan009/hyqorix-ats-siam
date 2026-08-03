import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { ref } from 'vue'

export const useAgentStore = defineStore('agent', () => {
  const { item, isModal, isViewModal, isEditModal, isDeleteModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Agent'
  const isLoading = ref(false)

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    isDeleteModal,
    moduleName,
    handleToggleModal,
    handleReset,
    isLoading,
  }
})
