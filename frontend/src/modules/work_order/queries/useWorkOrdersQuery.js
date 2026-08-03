import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchClients, fetchWorkOrderData } from '../services/workOrderService'
import { computed, unref } from 'vue'

/**
 * Work Order list
 */
export function useWorkOrdersQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const employeeId = computed(() => unref(filtersRef)?.employee_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'work-orders'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      clientId.value || 'all',
      employeeId.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        client_id: clientId.value,
        employee_id: employeeId.value,
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

export function useWorkOrderDataQuery() {
  return useQuery({
    queryKey: ['work-orders-data', 'v2'],
    queryFn: fetchWorkOrderData,
    staleTime: Infinity,
    meta: {
      persist: true,
    },
  })
}

/**
 * Client countries
 */
export function useWorkOrderClientsQuery() {
  return useQuery({
    queryKey: ['workorder-clients', 'v2'],
    queryFn: fetchClients,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
