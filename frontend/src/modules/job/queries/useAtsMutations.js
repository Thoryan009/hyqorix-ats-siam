import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {
  submitNextProcessData,
  submitBulkNextProcessData,
  updateProcessData,
  deleteProcessFn,
} from '../services/atsService'
import { toast } from '@/shared/config/toastConfig'

export function useAtsMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['jobs'])
    options.onSuccess?.(data, variables) // ✅ generic callback
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
    options.onError?.(error)
  }

  const createNextProcess = useMutation({
    mutationFn: submitNextProcessData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const updateFormProcess = useMutation({
    mutationFn: updateProcessData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const createBulkNextProcess = useMutation({
    mutationFn: submitBulkNextProcessData,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const deleteProcess = useMutation({
    mutationFn: deleteProcessFn,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    createNextProcess,
    updateFormProcess,
    createBulkNextProcess,
    deleteProcess,
    deleteProcessLoading: deleteProcess.isPending,
  }
}
