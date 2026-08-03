import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
  submitData,
  updateData,
  deleteItem,
  bulkDelete,
  generateModulePermissions,
} from '../services/permissionService'
import { toast } from '@/shared/config/toastConfig'

export function usePermissionMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['permissions'])
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

  const generateModule = useMutation({
    mutationFn: generateModulePermissions,
    onSuccess: (data, variables) => {
      const created = data?.data?.created_count ?? 0
      const skipped = data?.data?.skipped_count ?? 0
      toast.success(
        created > 0
          ? `Created ${created} permission${created === 1 ? '' : 's'}${skipped ? ` (${skipped} already existed)` : ''}`
          : skipped > 0
            ? `All selected permissions already exist (${skipped})`
            : `${moduleName} operation successful`
      )
      queryClient.invalidateQueries(['permissions'])
      options.onSuccess?.(data, variables)
    },
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
    generateModule,
    generateModuleLoading: generateModule.isPending,
    update,
    updateLoading: update.isPending,
    remove,
    removeLoading: remove.isPending,
    removeItems,
    removeItemsLoading: removeItems.isPending,
  }
}
