import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { submitData, updateData, deleteItem, bulkDelete } from '../services/documentService'
import { toast } from '@/shared/config/toastConfig'
import { useTranslate } from '@/shared/composables/useTranslate'

export function useDocumentMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()
  const { t } = useTranslate()

  const handleSuccess = (data, variables) => {
    toast.success(t('documents.operation_successful'))
    queryClient.invalidateQueries(['documents'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(
      t('documents.request_failed', {
        message: error?.message || t('documents.unknown_error'),
      }),
    )
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

  return {
    submit,
    submitLoading: submit.isPending,
    update,
    updateLoading: update.isPending,
    remove,
    removeLoading: remove.isPending,
    removeItems,
    removeItemsLoading: removeItems.isPending,
  }
}
