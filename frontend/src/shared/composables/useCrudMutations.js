import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
  submitData,
  updateData,
  updateStatusRequest,
  deleteItem,
  bulkDelete
} from '@/shared/services/crudService'

import { toast } from '@/shared/config/toastConfig'

export function useCrudMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries([moduleName])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
    options.onError?.(error)
  }

  const submit = useMutation({
    mutationFn: (payload) => submitData(moduleName, payload),
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const update = useMutation({
    mutationFn: (payload) => updateData(moduleName, payload),
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const updateStatus = useMutation({
    mutationFn: ({ id, status }) => updateStatusRequest(moduleName, id, status),
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const remove = useMutation({
    mutationFn: (id) => deleteItem(moduleName, id),
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const removeItems = useMutation({
    mutationFn: (ids) => bulkDelete(moduleName, ids),
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    submit,
    submitLoading: submit.isPending,

    update,
    updateLoading: update.isPending,

    updateStatus,
    updateStatusLoading: updateStatus.isPending,

    remove,
    removeLoading: remove.isPending,

    removeItems,
    removeItemsLoading: removeItems.isPending,
  }
}
