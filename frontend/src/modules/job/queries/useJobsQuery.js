import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchJoblistData } from '../services/jobService'
import { computed, unref } from 'vue'

/**
 * Job list
 */
export function useJobsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const workOrderId = computed(() => unref(filtersRef)?.work_order_id)
  const principalId = computed(() => unref(filtersRef)?.principal_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'jobs'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      workOrderId.value || 'all',
      principalId.value || 'all',
      clientId.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        work_order_id: workOrderId.value,
        principal_id: principalId.value,
        client_id: clientId.value,
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

/**
 * Job Work Orders
 */
export function useJobListDataQuery(clientIdRef = null) {
  const clientId = computed(() => unref(clientIdRef))

  return useQuery({
    queryKey: computed(() =>
      clientId.value ? ['job-work-orders', clientId.value, 'v2'] : ['job-work-orders', 'all', 'v2'],
    ),

    queryFn: () => fetchJoblistData(clientId.value),

    // staleTime: Infinity,
    cacheTime: 0,

    // always enabled (both cases valid)
    enabled: true,
  })
}
