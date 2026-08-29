import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { approveJournal, payJournal, resubmitJournal, returnJournal, submitJournal } from '../services/journalService'
import { toast } from '@/shared/config/toastConfig'

export function useJournalMutations(options = {}) {
  const queryClient = useQueryClient()

  const invalidateJournals = () => {
    queryClient.invalidateQueries({ queryKey: ['journals'] })
  }

  const handleError = (error) => {
    const message =
      error?.message ||
      error?.errors?.lines?.[0] ||
      error?.errors?.manager_comment?.[0] ||
      error?.errors?.status?.[0] ||
      Object.values(error?.errors ?? {})[0]?.[0] ||
      'Request Failed'
    toast.error(message)
    options.onError?.(error)
  }

  const submit = useMutation({
    mutationFn: submitJournal,
    onSuccess: (data, variables) => {
      invalidateJournals()
      options.onSuccess?.(data, variables)
    },
    onError: handleError,
  })

  const approve = useMutation({
    mutationFn: ({ id, ...payload }) => approveJournal(id, payload),
    onSuccess: (data, variables) => {
      invalidateJournals()
      options.onApproveSuccess?.(data, variables)
    },
    onError: handleError,
  })

  const returnJournalMutation = useMutation({
    mutationFn: ({ id, ...payload }) => returnJournal(id, payload),
    onSuccess: (data, variables) => {
      invalidateJournals()
      options.onReturnSuccess?.(data, variables)
    },
    onError: handleError,
  })

  const resubmit = useMutation({
    mutationFn: ({ id, ...payload }) => resubmitJournal(id, payload),
    onSuccess: (data, variables) => {
      invalidateJournals()
      options.onResubmitSuccess?.(data, variables)
    },
    onError: handleError,
  })

  const pay = useMutation({
    mutationFn: ({ id, ...payload }) => payJournal(id, payload),
    onSuccess: (data, variables) => {
      invalidateJournals()
      options.onPaySuccess?.(data, variables)
    },
    onError: handleError,
  })

  return {
    submit,
    submitLoading: submit.isPending,
    approve,
    approveLoading: approve.isPending,
    returnJournal: returnJournalMutation,
    returnLoading: returnJournalMutation.isPending,
    resubmit,
    resubmitLoading: resubmit.isPending,
    pay,
    payLoading: pay.isPending,
  }
}
