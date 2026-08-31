import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchGeneralLedger } from '../services/journalService'

export function useGeneralLedgerQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const filters = computed(() => unref(filtersRef) ?? {})

  return useQuery({
    queryKey: computed(() => [
      'general-ledger',
      page.value,
      perPage.value,
      filters.value.account_id || 'all',
      filters.value.party_ref || 'all',
      filters.value.searchQuery?.trim() || 'all',
      filters.value.from_date || 'all',
      filters.value.to_date || 'all',
    ]),
    queryFn: () =>
      fetchGeneralLedger(page.value, perPage.value, {
        account_id: filters.value.account_id || undefined,
        party_ref: filters.value.party_ref || undefined,
        search: filters.value.searchQuery?.trim() || undefined,
        from_date: filters.value.from_date || undefined,
        to_date: filters.value.to_date || undefined,
      }),
    staleTime: 30 * 1000,
    gcTime: 60 * 60 * 1000,
    placeholderData: (previousData) => previousData,
    refetchOnWindowFocus: false,
  })
}
