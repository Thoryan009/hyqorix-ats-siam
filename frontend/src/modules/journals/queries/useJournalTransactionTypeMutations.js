import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
  bulkDelete,
  deleteItem,
  submitData,
  updateData,
} from '../services/journalTransactionTypeService'
import { toast } from '@/shared/config/toastConfig'

export function useJournalTransactionTypeMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries({ queryKey: ['journal-transaction-types'] })
    queryClient.invalidateQueries({ queryKey: ['journal-transaction-type-options'] })
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    const validationMessage = error?.errors
      ? Object.values(error.errors).flat().find(Boolean)
      : null
    toast.error(`Request Failed: ${validationMessage || error?.message || 'Unknown error'}`)
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
