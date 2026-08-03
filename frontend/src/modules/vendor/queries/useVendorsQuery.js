import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll, fetchVendorData } from '../services/vendorService'

export function useVendorsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)

  return useQuery({
    queryKey: computed(() => [
      'vendors',
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

export function useVendorDataQuery() {
  return useQuery({
    queryKey: ['vendor-data'],
    queryFn: fetchVendorData,
    staleTime: Infinity,
    cacheTime: 24 * 60 * 60 * 1000,
    meta: {
      persist: true,
    },
  })
}
