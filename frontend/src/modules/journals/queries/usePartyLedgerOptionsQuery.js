import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { fetchAll } from '@/modules/parties/services/partyService'

export function usePartyLedgerOptionsQuery(partyTypeRef, options = {}) {
  const partyType = computed(() => unref(partyTypeRef) || '')
  const filters = computed(() => unref(options.filters) ?? {})
  const enabledOption = computed(() => unref(options.enabled) ?? true)

  const queryEnabled = computed(() => {
    if (!enabledOption.value) return false
    if (!partyType.value) return false
    return true
  })

  return useQuery({
    queryKey: computed(() => [
      'party-ledger-options',
      partyType.value || 'all',
      filters.value.job_list_id || '',
    ]),
    queryFn: () =>
      fetchAll(1, 500, {
        status: 'active',
        type: partyType.value || undefined,
        ...(filters.value.job_list_id ? { job_list_id: filters.value.job_list_id } : {}),
      }),
    enabled: queryEnabled,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
