import { useQuery } from '@tanstack/vue-query'
import { fetchWorkOrders, fetchAll, fetchOne, fetchClients, fetchAtsData } from '../services/atsService'
import { computed, unref } from 'vue'

export function useAtsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'ats'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      clientId.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
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

export function useSingleAtsQuery(jobIdRef = null, processIdRef = null, applicationIdRef = null) {
  const jobId = computed(() => unref(jobIdRef))
  const processId = computed(() => unref(processIdRef))
  const applicationId = computed(() => unref(applicationIdRef))

  return useQuery({
    queryKey: computed(() => [
      'single-ats',
      jobId.value ?? 'all',
      processId.value ?? 'all',
      applicationId.value ?? 'all',
    ]),

    queryFn: () => fetchOne(jobId.value, processId.value, applicationId.value),

    enabled: computed(() => !!jobId.value && !!processId.value),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,

    meta: {
      persist: true,
    },
  })
}

/**
 * Ats clients
 */
export function useAtsClientsQuery() {
  return useQuery({
    queryKey: ['ats-clients'],
    queryFn: fetchClients,
    staleTime: Infinity,
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}


/*
 * ATS Work Orders
 */
export function useAtsWorkOrdersQuery(filtersRef) {
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const cacheKey = 'ats-work-orders'

  return useQuery({
    queryKey: computed(() => [cacheKey, clientId.value || 'all']),

    queryFn: () => fetchWorkOrders(clientId.value),

    staleTime: Infinity,
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}

/**
 * Ats Data
 */
export function useAtsDataQuery() {
  const cacheKey = 'ats-data'

  return useQuery({
    queryKey: [cacheKey, 'v2'],
    queryFn: () => fetchAtsData(),
    staleTime: Infinity,
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
