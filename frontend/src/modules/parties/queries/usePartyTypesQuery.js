import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/partyTypeService'

export function usePartyTypesQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const status = computed(() => unref(filtersRef)?.status || '')

  return useQuery({
    queryKey: computed(() => [
      'party-types',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      status.value || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
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
