import { toast } from "@/shared/config/toastConfig"
import { useMutation, useQueryClient } from "@tanstack/vue-query"
import { bulkStatusUpdate } from "../services/tasheerAppointmentReportService"

export function useTasheerAppointmentMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['applications'])
    queryClient.invalidateQueries(['tasheer-appointment-report'])
    queryClient.invalidateQueries(['tasheer-appointment-filter-data'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
    options.onError?.(error)
  }

  const bulkStatusUpdateMutation = useMutation({
    mutationFn: bulkStatusUpdate,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    bulkStatusUpdateMutation,
  }
}
