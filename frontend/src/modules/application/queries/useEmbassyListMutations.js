import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { submitData, updateData, deleteItem, bulkDelete } from '../services/embassyListService'
import { toast } from '@/shared/config/toastConfig'

export function useEmbassyListMutations(moduleName = 'Embassy List', options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} saved successfully`)
    queryClient.invalidateQueries({ queryKey: ['embassy-lists'] })
    queryClient.invalidateQueries({ queryKey: ['embassy-list'] })
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    const message =
      Object.values(error?.errors || {})?.flat()?.[0] ||
      error?.message ||
      'Unknown error'

    toast.error(`Request Failed: ${message}`)
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
    onSuccess: () => {
      toast.success(`${moduleName} deleted successfully`)
      queryClient.invalidateQueries({ queryKey: ['embassy-lists'] })
    },
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
