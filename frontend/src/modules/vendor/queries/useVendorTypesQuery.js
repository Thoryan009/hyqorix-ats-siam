import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/vendorTypeService'

export function useVendorTypesQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const status = computed(() => unref(filtersRef)?.status)
  const fromDate = computed(() => unref(filtersRef)?.from_date)
  const toDate = computed(() => unref(filtersRef)?.to_date)

  return useQuery({
    queryKey: computed(() => [
      'vendor-types',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      status.value || 'all',
      fromDate.value || 'all',
      toDate.value || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        status: status.value,
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
