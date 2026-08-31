import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchPartyLedger } from '../services/journalService'

export function usePartyLedgerQuery(pageRef, perPageRef, filtersRef) {
  const page = computed(() => unref(pageRef))
  const perPage = computed(() => unref(perPageRef))
  const filters = computed(() => unref(filtersRef) ?? {})

  return useQuery({
    queryKey: computed(() => [
      'party-ledger',
      page.value,
      perPage.value,
      filters.value.party_type || 'all',
      filters.value.party_id || 'all',
      filters.value.party_ref || 'all',
      filters.value.searchQuery?.trim() || 'all',
      filters.value.from_date || 'all',
      filters.value.to_date || 'all',
    ]),
    queryFn: () =>
      fetchPartyLedger(page.value, perPage.value, {
        party_type: filters.value.party_type || undefined,
        party_id: filters.value.party_id || undefined,
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
