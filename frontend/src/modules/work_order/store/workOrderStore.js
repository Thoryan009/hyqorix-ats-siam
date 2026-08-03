import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { ref } from 'vue'

export const useWorkOrderStore = defineStore('workOrder', () => {
  const { item, isModal, isViewModal, isEditModal, isDeleteModal, handleToggleModal } =
    useModalHelpers()

  const moduleName = 'Demand Letter'

  const formData = ref({
    id: '',
    client_id: '',
    employee_id: '',
    candidates: '',
    end_date: '',
    visa_issue_number: null,
    sponsor_id: null,
    work_order_path: null,
    work_order_preview: null,
  })

  function handleReset(payload) {
    Object.keys(payload).forEach((key) => {
      if (key === 'price' || key === 'price_usd') {
        payload[key] = 0
      } else if (key.includes('_path') || key.includes('_preview')) {
        payload[key] = null
      } else if (key !== 'id') {
        payload[key] = ''
      }
    })
  }

  const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(
    formData.value,
  )

  const deleteImagePdfFiles = async (id, file_key) => {
    const { useWorkOrderMutations } = await import('../queries/useWorkOrderMutations')
    const { deleteFile } = useWorkOrderMutations()
    await deleteFile.mutateAsync({ id, file_key })
  }

  return {
    item,
    isModal,
    isViewModal,
    isEditModal,
    isDeleteModal,
    moduleName,
    handleToggleModal,
    handleReset,
    formData,
    handleFileChange,
    cancelImage,
    fileName,
    fileError,
    fileType,
    deleteImagePdfFiles,
  }
})
