import { useQuery } from '@tanstack/vue-query'
import { fetchAll, fetchFlightSummary } from '../services/DashboardService'
import { computed, unref } from 'vue'

export function useDashboardQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)
  const cacheKey = 'dashboard'

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

export function useFlightSummaryQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const countryId = computed(() => unref(filtersRef)?.country_id)
  const clientId = computed(() => unref(filtersRef)?.client_id)
  const departureFrom = computed(() => unref(filtersRef)?.departure_from)
  const departureTo = computed(() => unref(filtersRef)?.departure_to)

  return useQuery({
    queryKey: computed(() => [
      'flight-summary',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      countryId.value || 'all',
      clientId.value || 'all',
      departureFrom.value || 'all',
      departureTo.value || 'all',
    ]),

    queryFn: () =>
      fetchFlightSummary(page.value, perPage.value, {
        search: search.value,
        country_id: countryId.value,
        client_id: clientId.value,
        departure_from: departureFrom.value,
        departure_to: departureTo.value,
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
