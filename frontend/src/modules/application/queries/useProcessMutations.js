import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {  updateData } from '../services/processService'
import { toast } from '@/shared/config/toastConfig'

export function useProcessMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['processes'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
    options.onError?.(error)
  }


  const update = useMutation({
    mutationFn: updateData,
    onSuccess: handleSuccess,
    onError: handleError,
  })



  return {  update, updateLoading: update.isPending }
}
