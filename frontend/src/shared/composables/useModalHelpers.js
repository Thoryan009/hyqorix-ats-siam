import { computed, ref } from 'vue'

export function useModalHelpers(moduleName) {
  const item = ref(null)

  const type = ref('')
  const isModal = ref(false)
  const isViewModal = ref(false)
  const isEditModal = ref(false)

  function resetModals() {
    isModal.value = false
    isViewModal.value = false
    isEditModal.value = false
  }

  function handleToggleModal(modalType, selectedItem = null) {
    item.value = selectedItem
    type.value = modalType
    resetModals()

    if (modalType === 'add') isModal.value = true
    else if (modalType === 'view') isViewModal.value = true
    else if (modalType === 'edit') isEditModal.value = true
  }

   const title = computed(() => {
    if (isViewModal.value) return `View ${moduleName}`
    if (isEditModal.value) return `Edit ${moduleName}`
    return `Create ${moduleName}`
  })

  function handleReset(payload) {
    Object.keys(payload).forEach((key) => {
      payload[key] = ''
    })
  }

  return {
    // state
    item,
    type,
    isModal,
    isViewModal,
    isEditModal,
    title,

    // actions
    handleToggleModal,
    handleReset,
    resetModals,
  }
}
