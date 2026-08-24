import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { getPartySourceConfig, requiresPartyJobFilter } from '../data/partySourceConfig'

function extractRows(data) {
  return data?.data?.data ?? data?.data ?? []
}

export function usePartySourceOptionsQuery(partyTypeRef, sourceFiltersRef = null) {
  const partyType = computed(() => unref(partyTypeRef) || '')
  const sourceFilters = computed(() => unref(sourceFiltersRef) ?? {})

  const queryEnabled = computed(() => {
    const config = getPartySourceConfig(partyType.value)
    if (!config) return false

    if (requiresPartyJobFilter(partyType.value)) {
      return Boolean(sourceFilters.value.job_list_id)
    }

    return true
  })

  return useQuery({
    queryKey: computed(() => [
      'party-source-options',
      partyType.value,
      sourceFilters.value.job_list_id || 'all',
    ]),
    queryFn: async () => {
      const config = getPartySourceConfig(partyType.value)
      if (!config) return []

      const { data, error } = await config.fetch(sourceFilters.value)
      if (error) throw error

      return extractRows(data).map(config.mapItem)
    },
    enabled: queryEnabled,
    staleTime: 5 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    refetchOnWindowFocus: false,
  })
}
