import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { collectPassports, deleteItem, submitData } from '../services/passportHandoverService'
import { toast } from '@/shared/config/toastConfig'

export function usePassportHandoverMutations(moduleName = 'Passport Handover', options = {}) {
  const queryClient = useQueryClient()

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
    onSuccess: (data, variables) => {
      toast.success(`${moduleName} created successfully`)
      queryClient.invalidateQueries({ queryKey: ['passport-handovers'] })
      options.onSuccess?.(data, variables)
    },
    onError: handleError,
  })

  const remove = useMutation({
    mutationFn: deleteItem,
    onSuccess: () => {
      toast.success(`${moduleName} deleted successfully`)
      queryClient.invalidateQueries({ queryKey: ['passport-handovers'] })
    },
    onError: handleError,
  })

  const collect = useMutation({
    mutationFn: ({ id, payload }) => collectPassports(id, payload),
    onSuccess: () => {
      toast.success('Passport handover processed successfully')
      queryClient.invalidateQueries({ queryKey: ['passport-handovers'] })
      queryClient.invalidateQueries({ queryKey: ['passport-handover'] })
      options.onCollectSuccess?.()
    },
    onError: handleError,
  })

  return {
    submit,
    submitLoading: submit.isPending,
    remove,
    removeLoading: remove.isPending,
    collect,
    collectLoading: collect.isPending,
  }
}
