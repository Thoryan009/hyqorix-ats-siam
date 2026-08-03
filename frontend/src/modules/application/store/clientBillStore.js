import { ref } from 'vue'
import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { useFileHandler } from '@/shared/composables/useFileHandler'

export const useClientBillStore = defineStore('clientBill', () => {
  const { item, isModal, isViewModal, isEditModal, isDeleteModal, handleToggleModal, handleReset } =
    useModalHelpers()

  const moduleName = 'Client Bill'
  const activeTab = ref('') // store the value of the active tab
  // Set the active tab
  const setActiveTab = (tabValue) => {
    activeTab.value = tabValue
  }

  const formData = ref({
    to_mail: '',
    subject: '',
    body: '',
    invoice_preview: null,
    bill_no: '',
    client_id: ''
  })

  const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(
    formData.value,
  )

  const showEmailForm = ref(false)

  const handleToggleEmailForm = () => {
    showEmailForm.value = !showEmailForm.value
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
    showEmailForm,
    handleToggleEmailForm,
    formData,
    handleFileChange,
    fileName,
    cancelImage,
    fileError,
    fileType,
    activeTab,
    setActiveTab,
  }
})
