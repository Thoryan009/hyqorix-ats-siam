import { useQuery } from '@tanstack/vue-query'
import { computed, unref } from 'vue'
import { getPartySourceConfig, requiresPartyJobFilter } from '../data/partySourceConfig'

function extractRows(payload) {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.data?.data)) return payload.data.data
  return []
}

export function usePartySourceOptionsQuery(
  partyTypeRef,
  sourceFiltersRef = null,
  enabledRef = true,
) {
  const partyType = computed(() => unref(partyTypeRef) || '')
  const sourceFilters = computed(() => unref(sourceFiltersRef) ?? {})
  const extraEnabled = computed(() => Boolean(unref(enabledRef)))

  const queryEnabled = computed(() => {
    if (!extraEnabled.value) return false

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
    staleTime: 0,
    gcTime: 5 * 60 * 1000,
    refetchOnMount: 'always',
    refetchOnWindowFocus: false,
  })
}
