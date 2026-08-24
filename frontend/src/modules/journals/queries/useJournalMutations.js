import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { submitJournal } from '../services/journalService'
import { toast } from '@/shared/config/toastConfig'

export function useJournalMutations(options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    queryClient.invalidateQueries({ queryKey: ['journals'] })
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    const message =
      error?.message ||
      error?.errors?.lines?.[0] ||
      Object.values(error?.errors ?? {})[0]?.[0] ||
      'Request Failed'
    toast.error(message)
    options.onError?.(error)
  }

  const submit = useMutation({
    mutationFn: submitJournal,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  return {
    submit,
    submitLoading: submit.isPending,
  }
}
