import { defineStore } from 'pinia'
import { useModalHelpers } from '@/shared/composables/useModalHelpers'
import { useFileHandler } from '@/shared/composables/useFileHandler'
import { ref } from 'vue'
import { useApplicationMutations } from '../queries/useApplicationMutations'
import app from '@/shared/config/appConfig'

export const useApplicationStore = defineStore('application', () => {
  const openBulkUploadModal = ref(false)

  const handleToggleModalBulkUpload = () => {
    openBulkUploadModal.value = !openBulkUploadModal.value
  }

  const { deleteFile } = useApplicationMutations()

  const moduleName = 'Applicant'

  const defaultFormData = {
    sur_name: 'Ahmed',
    given_name: 'Rahim',
    marital_status: 'single',
    date_of_birth: '1995-06-15',
    sex: 'male',
    nationality: 'Bangladeshi',
    language: 'Basic English',
    place_of_birth: 'Dhaka',

    mobile: '01798755329',
    email: 'rahim.ahmed@example.com',
    father_name: 'Abdul Ahmed',
    mother_name: 'Salma Ahmed',
    address: '123 Main Street, Dhaka, Bangladesh',
    height: '5.8',
    weight: '75',

    qualification: 'B.Sc in CSE',

    // Experiences array
    experiences: [],

    nid_no: '1234567890',
    nid_link: 'https://example.com/nid',
    nid_preview: null,

    passport_no: 'E12345678',
    date_of_issue: '2020-05-01',
    date_of_expiry: '2030-05-01',
    passport_link: 'https://example.com/passport',
    passport_preview: null,
    passport_pdf_preview: null,

    resume_link: 'https://example.com/resume.pdf',
    resume_preview: null,

    remarks: 'NID preview missing. Applicant requested to re-upload document.',
    job_list_id: 1,
    applied_through: 'agent',
    agent_id: 1,
    payment_responsibility: ['agent'],

    education_preview: null,
    training_preview: null,
    experience_preview: null,
    driving_license_preview: null,

    // other details
    summary: '',
    documents_preview: null,
    offer_letter_preview: null,
    acknowledgment_preview: null,
    worker_image_preview: null,

    driving_license_no: 'DL12345678',
    qualification_id: 1,
    subject_id: 1,

    // single combined document
    single_document_preview: null,

    create_party_account: 0,
  }

  const formData = ref(
    app.moduleLocal
      ? { ...defaultFormData }
      : Object.fromEntries(
          Object.keys(defaultFormData).map((key) => [
            key,
            key === 'nationality' || key === 'language' || key === 'sex'
              ? defaultFormData[key] // keep default
              : key === 'experiences'
                ? [] // initialize experiences as empty array
                : key === 'payment_responsibility'
                  ? ['agent']
                  : key === 'applied_through'
                    ? 'agent'
                    : key === 'create_party_account'
                      ? 0
                      : '', // reset everything else
          ]),
        ),
  )

  function handleReset(payload) {
    // Keys we want to preserve
    const preserveKeys = ['nationality', 'language', 'sex']

    Object.keys(payload).forEach((key) => {
      // Only reset if key is NOT in preserveKeys
      if (!preserveKeys.includes(key)) {
        if (key === 'experiences') {
          payload[key] = [] // reset experiences to empty array
        } else if (key === 'payment_responsibility') {
          payload[key] = ['agent']
        } else if (key === 'applied_through') {
          payload[key] = 'agent'
        } else if (key === 'create_party_account') {
          payload[key] = 0
        } else {
          payload[key] = ''
        }
      }
    })
  }

  const { handleFileChange, fileName, cancelImage, fileError, fileType } = useFileHandler(
    formData.value,
  )

  const deleteImagePdfFiles = async (id, file_key) => {
    await deleteFile.mutateAsync({ id, file_key })
  }

  const { item, isModal, isViewModal, type, isEditModal, isDeleteModal, handleToggleModal } =
    useModalHelpers(moduleName)

  return {
    item,
    type,
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
    openBulkUploadModal,
    handleToggleModalBulkUpload,
  }
})
