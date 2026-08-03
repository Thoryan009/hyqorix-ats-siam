import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchPrincipalData } from '../services/principalService'
import { computed, unref } from 'vue'

/**
 * Principals list
 */
export function usePrincipalsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const user_id = computed(() => unref(filtersRef)?.user_id)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'principals'

  return useQuery({
    queryKey: computed(() => [
      cacheKey,
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),

    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        from_date: fromDate.value,
        to_date: toDate.value,
        user_id: user_id.value,
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

export function usePrincipalDataQuery() {
  const cacheKey = 'principal-data'

  return useQuery({
    queryKey: computed(() => [cacheKey]),
    queryFn: () => fetchPrincipalData(),
    staleTime: 5 * 60 * 1000,
    cacheTime: 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
