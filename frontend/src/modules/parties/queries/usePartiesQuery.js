import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/partyService'

export function usePartiesQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const type = computed(() => unref(filtersRef)?.type || '')
  const status = computed(() => unref(filtersRef)?.status || '')

  return useQuery({
    queryKey: computed(() => [
      'parties',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      type.value || 'all',
      status.value || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        type: type.value || undefined,
        status: status.value || undefined,
      }),
    enabled: computed(() => !search.value || search.value.length >= 3),
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    placeholderData: (previousData) => previousData,
    refetchOnWindowFocus: false,
    meta: {
      persist: true,
    },
  })
}
