import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchClientData } from '../services/clientService'
import { computed, unref } from 'vue'

/**
 * Clients list
 */
export function useClientsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const countryId = computed(() => unref(filtersRef)?.country_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'clients'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      countryId.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        country_id: countryId.value,
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
 * Client data
 */
export function useClientDataQuery() {
  return useQuery({
    queryKey: ['client-data'],
    queryFn: fetchClientData,
    staleTime: Infinity, // rarely changes
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
