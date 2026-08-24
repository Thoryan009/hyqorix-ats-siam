import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '../services/journalService'

export function useJournalsQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const search = computed(() => unref(filtersRef)?.searchQuery?.trim())
  const status = computed(() => unref(filtersRef)?.status || '')
  const transactionType = computed(() => unref(filtersRef)?.transaction_type || '')

  return useQuery({
    queryKey: computed(() => [
      'journals',
      page.value,
      perPage.value,
      search.value && search.value.length >= 3 ? search.value : 'all',
      status.value || 'all',
      transactionType.value || 'all',
      unref(filtersRef)?.from_date || 'all',
      unref(filtersRef)?.to_date || 'all',
    ]),
    queryFn: () =>
      fetchAll(page.value, perPage.value, {
        search: search.value,
        status: status.value || undefined,
        transaction_type: transactionType.value || undefined,
        from_date: unref(filtersRef)?.from_date || undefined,
        to_date: unref(filtersRef)?.to_date || undefined,
      }),
    enabled: computed(() => !search.value || search.value.length >= 3),
    staleTime: 30 * 1000,
    gcTime: 60 * 60 * 1000,
    placeholderData: (previousData) => previousData,
    refetchOnWindowFocus: false,
  })
}
