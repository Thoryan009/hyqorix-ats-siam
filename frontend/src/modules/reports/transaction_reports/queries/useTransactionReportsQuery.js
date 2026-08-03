import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchOne } from '../services/transactionReportService'
import { computed, unref } from 'vue'

export function useTransactionReportsQuery(filtersRef) {
  const status = computed(() => unref(filtersRef)?.status)
  const paymentMethod = computed(() => unref(filtersRef)?.payment_method)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const type = computed(() => unref(filtersRef)?.type)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'transaction-reports'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      status.value || 'all',
      paymentMethod.value || 'all',
      workOrderId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      type.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll({
        status: status.value,
        payment_method: paymentMethod.value,
        work_order_id: workOrderId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
        type: type.value,
        from_date: fromDate.value,
        to_date: toDate.value,
      }),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,

    meta: {
      persist: true,
    },
  })
}

export function useTransactionReportQuery(filtersRef) {
  const status = computed(() => unref(filtersRef)?.status)
  const paymentMethod = computed(() => unref(filtersRef)?.payment_method)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const type = computed(() => unref(filtersRef)?.type)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'transaction-reports'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      status.value || 'all',
      paymentMethod.value || 'all',
      workOrderId.value || 'all',
      jobId.value || 'all',
      clientId.value || 'all',
      type.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchOne({
        status: status.value,
        payment_method: paymentMethod.value,
        work_order_id: workOrderId.value,
        job_list_id: jobId.value,
        client_id: clientId.value,
        type: type.value,
        payment_from: fromDate.value,
        payment_to: toDate.value,
      }),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,

    meta: {
      persist: true,
    },
  })
}
