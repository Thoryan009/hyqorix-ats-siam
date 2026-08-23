import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '@/modules/parties/services/partyService'

export function usePartyLedgerOptionsQuery(partyTypeRef) {
  const partyType = computed(() => unref(partyTypeRef) || '')

  return useQuery({
    queryKey: computed(() => ['party-ledger-options', partyType.value || 'all']),
    queryFn: () =>
      fetchAll(1, 500, {
        status: 'active',
        type: partyType.value || undefined,
      }),
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
