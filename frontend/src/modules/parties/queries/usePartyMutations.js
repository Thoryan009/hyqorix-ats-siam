import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { bulkDelete, deleteItem, submitData, updateData } from '../services/partyService'
import { toast } from '@/shared/config/toastConfig'

export function usePartyMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['parties'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
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
