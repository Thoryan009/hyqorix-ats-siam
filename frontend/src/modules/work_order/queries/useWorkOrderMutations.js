import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { submitData, updateData, deleteItem, bulkDelete, deleteImagePdfFiles } from '../services/workOrderService'
import { toast } from '@/shared/config/toastConfig'

export function useWorkOrderMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['work-orders'])
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
  const deleteFile = useMutation({
    mutationFn: deleteImagePdfFiles,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    submit,
    update,
    remove,
    removeItems,
    deleteFile,
    submitLoading: submit.isPending,
    updateLoading: update.isPending,
    removeLoading: remove.isPending,
    removeItemsLoading: removeItems.isPending,
  }
}
