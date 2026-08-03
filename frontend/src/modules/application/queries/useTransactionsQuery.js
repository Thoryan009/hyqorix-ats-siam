import { useQuery } from '@tanstack/vue-query'
import { fetchPosTransactions, fetchAll, fetchOne, fetchTransactionData } from '../services/transactionService'
import { computed, unref } from 'vue'

export function useTransactionsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const billNo = computed(() => unref(filtersRef)?.bill_no)
  const jobListId = computed(() => unref(filtersRef)?.job_list_id)
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const status = computed(() => unref(filtersRef)?.status)
  const payer = computed(() => unref(filtersRef)?.payer)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'transactions'
  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      billNo.value || 'all',
      jobListId.value || 'all',
      workOrderId.value || 'all',
      clientId.value || 'all',
      status.value || 'all',
      payer.value || 'all',
      search.value && search.value.length >= 3 ? search.value : 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        from_date: fromDate.value,
        to_date: toDate.value,
        bill_no: billNo.value,
        job_list_id: jobListId.value,
        work_order_id: workOrderId.value,
        client_id: clientId.value,
        status: status.value,
        payer: payer.value,
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

export function useSingleTransactionQuery(transactionId) {
  return useQuery({
    queryKey: computed(() => ['transaction', transactionId]),

    queryFn: () => fetchOne(transactionId),

    enabled: computed(() => !!transactionId),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,

    meta: {
      persist: true,
    },
  })
}

/**
 * Client countries
 */
export function usePosQuery(searchRef) {
  const search = computed(() => unref(searchRef)?.trim())
  const cacheKey = 'pos-transactions'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      search.value && search.value.length >= 3 ? search.value : '',
    ]),

    queryFn: () =>
      fetchPosTransactions({
        search: search.value,
      }),

    enabled: computed(() => !search.value || search.value.length >= 3),

    // 🟢 Cache behavior
    staleTime: 5 * 60 * 1000, // 5 minutes fresh
    cacheTime: 30 * 60 * 1000, // 30 minutes in cache

    keepPreviousData: true,
    refetchOnMount: false,
    refetchOnWindowFocus: false,

    meta: {
      persist: false,
    },
  })

}

  export function useTransactionQuery() {
    return useQuery({
      queryKey: ['transaction-data'],
      queryFn: fetchTransactionData,
      staleTime: Infinity,
      meta: {
        persist: true,
      },
    })
  }

