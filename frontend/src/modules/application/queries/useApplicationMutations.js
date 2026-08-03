import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
  submitData,
  updateData,
  updateEmbassySubmissionData,
  deleteEmbassySubmission,
  deleteItem,
  bulkDelete,
  bulkOfferExtendData,
  bulkStatusUpdate,
  bulkStatusUpdateEmbassySubmission,
  deleteImagePdfFiles,
  bulkUpload,
  passportOCRFn,
  checkPassportExistsFn,
  mergeDocuments,
} from '../services/applicationService'
import { toast } from '@/shared/config/toastConfig'

export function getMutationErrorMessage(error) {
  if (!error) return 'Something went wrong. Please try again.'
  if (typeof error === 'string') return error

  if (error.errors && typeof error.errors === 'object') {
    const first = Object.values(error.errors).flat().find(Boolean)
    if (first) return first
  }

  if (error.message && error.message !== 'The given data was invalid.') {
    return error.message
  }

  if (error.message) return error.message

  return 'Something went wrong. Please try again.'
}

export function buildApplicationPageMessage(action, formData, errorMessage = null) {
  const name = [formData?.sur_name, formData?.given_name].filter(Boolean).join(' ').trim() || 'N/A'
  const passportNo = formData?.passport_no?.trim() || 'N/A'
  const applicant = `${name} (Passport: ${passportNo})`

  if (errorMessage) {
    return `Failed to ${action} application for ${applicant}. ${errorMessage}`
  }

  return `Application ${action} successfully for ${applicant}.`
}

export function useApplicationMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()
  const showToast = options.showToast !== false

  const handleSuccess = (data, variables) => {
    if (showToast) {
      toast.success(`${moduleName} operation successful`)
    }
    queryClient.invalidateQueries(['applications'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    if (showToast) {
      toast.error(`Request Failed: ${getMutationErrorMessage(error)}`)
    }
    options.onError?.(error)
  }

  const submit = useMutation({
    mutationFn: submitData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const update = useMutation({
    mutationFn: updateData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const updateEmbassySubmission = useMutation({
    mutationFn: updateEmbassySubmissionData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const deleteEmbassySubmissionMutation = useMutation({
    mutationFn: deleteEmbassySubmission,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  // NOTE: creation of embassy submission is handled by updateEmbassySubmission
  const remove = useMutation({
    mutationFn: deleteItem,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const removeItems = useMutation({
    mutationFn: bulkDelete,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const bulkOfferExtend = useMutation({
    mutationFn: bulkOfferExtendData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const bulkStatusUpdateMutation = useMutation({
    mutationFn: bulkStatusUpdate,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const bulkStatusUpdateEmbassySubmissionMutation = useMutation({
    mutationFn: bulkStatusUpdateEmbassySubmission,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const bulkApplicationUpload = useMutation({
    mutationFn: bulkUpload,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const deleteFile = useMutation({
    mutationFn: deleteImagePdfFiles,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const passportOCR = useMutation({
    mutationFn: passportOCRFn,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const checkPassportExists = useMutation({
    mutationFn: checkPassportExistsFn,
  })

  const mergeDocumentsMutation = useMutation({
    mutationFn: mergeDocuments,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    submit,
    submitLoading: submit.isPending,
    update,
    updateLoading: update.isPending,
    remove,
    removeLoading: remove.isPending,
    removeItems,
    removeItemsLoading: removeItems.isPending,
    bulkOfferExtend,
    bulkStatusUpdateMutation,
    bulkStatusUpdateLoading: bulkStatusUpdateMutation.isPending,
    bulkStatusUpdateEmbassySubmissionMutation,
    bulkStatusUpdateEmbassySubmissionLoading: bulkStatusUpdateEmbassySubmissionMutation.isPending,
    deleteFile,
    bulkApplicationUpload,
    bulkApplicationUploadLoading: bulkApplicationUpload.isPending,
    passportOCR,
    passportOCRLoading: passportOCR.isPending,
    checkPassportExists,
    checkPassportExistsLoading: checkPassportExists.isPending,
    mergeDocumentsMutation,
    mergeDocumentsLoading: mergeDocumentsMutation.isPending,
    updateEmbassySubmission,
    updateEmbassySubmissionLoading: updateEmbassySubmission.isPending,
    deleteEmbassySubmissionMutation,
    deleteEmbassySubmissionLoading: deleteEmbassySubmissionMutation.isPending,
  }
}
