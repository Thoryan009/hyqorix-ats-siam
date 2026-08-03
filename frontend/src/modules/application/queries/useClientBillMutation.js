import { useMutation, useQueryClient } from '@tanstack/vue-query'
import {cancelledInvoiceFn, collectInvoiceFn, generateInvoice, sendMailToClient} from '../services/clientBillService'
import { toast } from '@/shared/config/toastConfig'

export function useClientBillMutations(moduleName, options = {}) {
  const queryClient = useQueryClient()

  const handleSuccess = (data, variables) => {
    toast.success(`${moduleName} operation successful`)
    queryClient.invalidateQueries(['client-bills'])
    options.onSuccess?.(data, variables)
  }

  const handleError = (error) => {
    console.error(error)
    toast.error(`Request Failed: ${error?.message || 'Unknown error'}`)
    options.onError?.(error)
  }

  const sendMail = useMutation({
      mutationFn: sendMailToClient,
      onSuccess: handleSuccess,
      onError: handleError,
    })

  const generateClientInvoice = useMutation({
    mutationFn: generateInvoice,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const collectInvoice = useMutation({
    mutationFn: collectInvoiceFn,
    onSuccess: handleSuccess,
    onError: handleError,
  })

  const cancelledInvoice = useMutation({
    mutationFn: cancelledInvoiceFn,
    onSuccess: handleSuccess,
    onError: handleError,
  });

  return { sendMail, generateClientInvoice, collectInvoice, cancelledInvoice, isLoading: generateClientInvoice.isPending, isMailing: sendMail.isPending, isCollecting: collectInvoice.isPending, isCancelling: cancelledInvoice.isPending }
}
