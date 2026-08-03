import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll, fetchByBillNo, fetchWorkOrders } from '../services/clientBillService'

export function useClientBillsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const jobId = computed(() => unref(filtersRef)?.job_id)
  const billNo = computed(() => unref(filtersRef)?.bill_no)
  const transactionStatus = computed(() => unref(filtersRef)?.transaction_status)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'client-bills'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      jobId.value || 'all',
      billNo.value || 'all',
      transactionStatus.value || 'bill-generated',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        job_id: jobId.value,
        bill_no: billNo.value,
        transaction_status: transactionStatus.value,
        from_date: fromDate.value,
        to_date: toDate.value,
      }),

    enabled: computed(() => !search.value || search.value.length >= 3),

    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    keepPreviousData: true,

    meta: {
      persist: true,
    },
  })
}

export function useSingleClientBillQuery(params) {
  const billNo = computed(() => params.bill_no)
  const jobId = computed(() => params.job_id)
  return useQuery({
    queryKey: computed(() => ['single-client-bill', billNo, jobId]),

    queryFn: () => fetchByBillNo(billNo.value, jobId.value),

    enabled: computed(() => !!billNo.value && !!jobId.value),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,

    meta: {
      persist: true,
    },
  })
}

/**
 * Client bill jobs
 */
export function useClientBillsDataQuery() {
  return useQuery({
    queryKey: ['client-bills-data'],
    queryFn: fetchWorkOrders,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
